<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DepositReceipt;
use App\Models\DepositItem;
use App\Models\InvestmentInterest;
use App\Models\Cashbook;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:Admin']);
    }

    public function index(Request $request)
    {
        $start = $request->date('start_date');
        $end   = $request->date('end_date');
        $types = collect((array) $request->input('types', ['deposit','interest','invest_out','invest_return','other_income','expense']))
            ->intersect(['deposit','interest','invest_out','invest_return','other_income','expense'])
            ->values();

        $items = collect();

        // Deposits (sum per receipt)
        if ($types->contains('deposit')) {
            $q = DepositReceipt::query();
            if ($start) $q->whereDate('date', '>=', $start);
            if ($end)   $q->whereDate('date', '<=', $end);
            $q->with(['member']);
            $receipts = $q->orderBy('date')->get();
            $depositRows = $receipts->map(function($r){
                return [
                    'date' => $r->date->format('Y-m-d'),
                    'type' => 'deposit',
                    'label' => 'Deposit: '.($r->member->name ?? '—'),
                    'in' => (float) $r->total_amount,
                    'out' => 0.0,
                    'meta' => [ 'id' => $r->id ],
                ];
            });
            $items = $items->concat($depositRows);
        }

        // Investment Outflow
        if ($types->contains('invest_out')) {
            $q = \App\Models\Investment::query();
            if ($start) $q->whereDate('date', '>=', $start);
            if ($end)   $q->whereDate('date', '<=', $end);
            $invests = $q->orderBy('date')->get();
            $rowsOut = $invests->map(function($inv){
                return [
                    'date' => $inv->date ? $inv->date->format('Y-m-d') : null,
                    'type' => 'invest_out',
                    'label' => 'Investment Outflow: '.($inv->title ?? '—'),
                    'in' => 0.0,
                    'out' => (float) $inv->amount,
                    'meta' => [ 'id' => $inv->id ],
                ];
            });
            $items = $items->concat($rowsOut);
        }

        // Investment Return
        if ($types->contains('invest_return')) {
            $qr = \App\Models\Investment::query()->whereNotNull('return_amount');
            if ($start) $qr->whereDate('return_date', '>=', $start);
            if ($end)   $qr->whereDate('return_date', '<=', $end);
            $returns = $qr->orderBy('return_date')->get();
            $rowsRet = $returns->map(function($inv){
                return [
                    'date' => $inv->return_date ? $inv->return_date->format('Y-m-d') : null,
                    'type' => 'invest_return',
                    'label' => 'Investment Return: '.($inv->title ?? '—'),
                    'in' => (float) $inv->return_amount,
                    'out' => 0.0,
                    'meta' => [ 'id' => $inv->id ],
                ];
            });
            $items = $items->concat($rowsRet);
        }

        // Investment Interest (income)
        if ($types->contains('interest')) {
            $q = InvestmentInterest::query();
            if ($start) $q->whereDate('date', '>=', $start);
            if ($end)   $q->whereDate('date', '<=', $end);
            $q->with('investment');
            $interests = $q->orderBy('date')->get();
            $rows = $interests->map(function($ii){
                return [
                    'date' => $ii->date->format('Y-m-d'),
                    'type' => 'interest',
                    'label' => 'Interest: '.($ii->investment->title ?? '—'),
                    'in' => (float) $ii->amount,
                    'out' => 0.0,
                    'meta' => [ 'id' => $ii->id ],
                ];
            });
            $items = $items->concat($rows);
        }

        // Other Income (Cashbook income not system categories)
        if ($types->contains('other_income')) {
            $systemIncomeCats = ['subscription','extra','fine','interest','investment return'];
            $q = Cashbook::where('type','income')
                ->whereRaw('LOWER(category) NOT IN ('.implode(',', array_fill(0,count($systemIncomeCats),'?')).')', $systemIncomeCats);
            if ($start) $q->whereDate('date', '>=', $start);
            if ($end)   $q->whereDate('date', '<=', $end);
            $incs = $q->orderBy('date')->get();
            $rows = $incs->map(function($c){
                return [
                    'date' => date('Y-m-d', strtotime($c->date)),
                    'type' => 'other_income',
                    'label' => 'Other Income: '.($c->category ?: '—'),
                    'in' => (float) $c->amount,
                    'out' => 0.0,
                    'meta' => [ 'id' => $c->id ],
                ];
            });
            $items = $items->concat($rows);
        }

        // Expenses (Cashbook expense)
        if ($types->contains('expense')) {
            $q = Cashbook::where('type','expense');
            if ($start) $q->whereDate('date', '>=', $start);
            if ($end)   $q->whereDate('date', '<=', $end);
            $exps = $q->orderBy('date')->get();
            $rows = $exps->map(function($c){
                return [
                    'date' => date('Y-m-d', strtotime($c->date)),
                    'type' => 'expense',
                    'label' => 'Expense: '.($c->category ?: '—'),
                    'in' => 0.0,
                    'out' => (float) $c->amount,
                    'meta' => [ 'id' => $c->id ],
                ];
            });
            $items = $items->concat($rows);
        }

        // Sort and totals
        $items = $items->sortBy('date')->values();
        $totalIn  = $items->sum('in');
        $totalOut = $items->sum('out');
        $balance  = $totalIn - $totalOut;

        // PDF export (requires barryvdh/laravel-dompdf installed)
        if ($request->get('export') === 'pdf') {
            if (class_exists('Dompdf\\Dompdf')) {
                $html = view('reports.pdf', [
                    'items' => $items,
                    'totalIn' => $totalIn,
                    'totalOut' => $totalOut,
                    'balance' => $balance,
                    'start' => $start?->format('Y-m-d'),
                    'end' => $end?->format('Y-m-d'),
                    'types' => $types->all(),
                ])->render();

                $dompdf = new \Dompdf\Dompdf([ 'isRemoteEnabled' => true ]);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $filename = 'report_'.now()->format('Ymd_His').'.pdf';
                return response($dompdf->output(), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="'.$filename.'"'
                ]);
            }
            return back()->with('status', 'PDF export not available. Please install barryvdh/laravel-dompdf.');
        }

        // CSV export
        if ($request->get('export') === 'csv') {
            $filename = 'report_'.now()->format('Ymd_His').'.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];
            $callback = function() use ($items, $totalIn, $totalOut, $balance) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Date','Type','Label','In','Out']);
                foreach ($items as $row) {
                    fputcsv($out, [$row['date'], $row['type'], $row['label'], number_format($row['in'],2,'.',''), number_format($row['out'],2,'.','')]);
                }
                fputcsv($out, []);
                fputcsv($out, ['Totals','','', number_format($totalIn,2,'.',''), number_format($totalOut,2,'.','')]);
                fputcsv($out, ['Balance','','', number_format($balance,2,'.',''), '']);
                fclose($out);
            };
            return response()->stream($callback, 200, $headers);
        }

        return view('reports.index', [
            'items' => $items,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'balance' => $balance,
            'start' => $start?->format('Y-m-d'),
            'end' => $end?->format('Y-m-d'),
            'types' => $types->all(),
        ]);
    }
}
