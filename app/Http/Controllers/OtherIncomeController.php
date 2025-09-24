<?php

namespace App\Http\Controllers;

use App\Models\Cashbook;
use App\Models\Setting;
use Illuminate\Http\Request;

class OtherIncomeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // Admin + Accountant can view and add; edit/update allowed for Admin always and for Accountant when enabled via Tools; delete Admin only
        $this->middleware(['role:Admin|Accountant'])->only(['index','create','store','edit','update']);
        $this->middleware(['role:Admin'])->only(['destroy']);
    }

    public function index()
    {
        $systemCats = ['subscription','extra','fine','interest','investment return'];
        $query = Cashbook::where('type','income')
            ->whereRaw('LOWER(category) NOT IN ('.implode(',', array_fill(0, count($systemCats), '?')).')', $systemCats)
            ->latest('date');

        if (request('category')) {
            $query->where('category', request('category'));
        }
        if (request('month')) {
            $query->whereMonth('date', request('month'));
        }
        if (request('year')) {
            $query->whereYear('date', request('year'));
        }
        if (request('search')) {
            $search = strtolower(trim(request('search')));
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(category) LIKE ?', ["%{$search}%"]) 
                  ->orWhereRaw('LOWER(note) LIKE ?', ["%{$search}%"]);
            });
        }

        $incomes = $query->with('addedBy')->paginate(15)->withQueryString();
        $total = (float) (clone $query)->sum('amount');

        $categories = Cashbook::where('type','income')
            ->whereRaw('LOWER(category) NOT IN ('.implode(',', array_fill(0, count($systemCats), '?')).')', $systemCats)
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        if (request()->ajax() || request('ajax')) {
            return view('other_incomes.partials.table', compact('incomes','total'));
        }

        // Dynamic years from other income dates
        $minDate = Cashbook::where('type','income')
            ->whereRaw('LOWER(category) NOT IN ('.implode(',', array_fill(0, count($systemCats), '?')).')', $systemCats)
            ->min('date');
        $maxDate = Cashbook::where('type','income')
            ->whereRaw('LOWER(category) NOT IN ('.implode(',', array_fill(0, count($systemCats), '?')).')', $systemCats)
            ->max('date');
        $yearStart = $minDate ? (int) date('Y', strtotime($minDate)) : 2019;
        $yearEnd = max((int) date('Y'), $maxDate ? (int) date('Y', strtotime($maxDate)) : (int) date('Y')) + 1;

        return view('other_incomes.index', compact('incomes','total','categories','yearStart','yearEnd'));
    }

    public function create()
    {
        $systemCats = ['subscription','extra','fine','interest','investment return'];
        $categories = Cashbook::where('type','income')
            ->whereRaw('LOWER(category) NOT IN ('.implode(',', array_fill(0, count($systemCats), '?')).')', $systemCats)
            ->select('category')->distinct()->orderBy('category')->pluck('category');
        return view('other_incomes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required','string','max:150'],
            'date' => ['required','date'],
            'category' => ['required','string','max:100'],
            'amount' => ['required','numeric','min:0'],
            'note' => ['nullable','string','max:255'],
        ]);

        $combinedNote = trim($data['title'] . (isset($data['note']) && $data['note'] !== '' ? ' — '.$data['note'] : ''));

        Cashbook::create([
            'date' => $data['date'],
            'type' => 'income',
            'category' => $data['category'],
            'amount' => $data['amount'],
            'note' => $combinedNote,
            'added_by' => auth()->id(),
        ]);

        return redirect()->route('other-incomes.index')->with('status','Income added');
    }

    public function edit(Cashbook $income)
    {
        abort_unless($income->type === 'income', 404);
        // Tools toggle: block Accountant if not enabled
        if (auth()->user()->hasRole('Accountant')) {
            $enabled = (bool) optional(Setting::first())->allow_accountant_edit_other_income;
            abort_unless($enabled, 403);
        }
        $categories = Cashbook::where('type','income')->select('category')->distinct()->orderBy('category')->pluck('category');
        return view('other_incomes.edit', ['income' => $income, 'categories' => $categories]);
    }

    public function update(Request $request, Cashbook $income)
    {
        abort_unless($income->type === 'income', 404);
        // Tools toggle: block Accountant if not enabled
        if (auth()->user()->hasRole('Accountant')) {
            $enabled = (bool) optional(Setting::first())->allow_accountant_edit_other_income;
            abort_unless($enabled, 403);
        }
        $data = $request->validate([
            'title' => ['required','string','max:150'],
            'date' => ['required','date'],
            'category' => ['required','string','max:100'],
            'amount' => ['required','numeric','min:0'],
            'note' => ['nullable','string','max:255'],
        ]);

        $combinedNote = trim($data['title'] . (isset($data['note']) && $data['note'] !== '' ? ' — '.$data['note'] : ''));

        // Before snapshot
        $before = [
            'date' => optional($income->date)->format('Y-m-d'),
            'category' => $income->category,
            'amount' => $income->amount,
            'note' => $income->note,
            'type' => 'income',
        ];

        $income->update([
            'date' => $data['date'],
            'category' => $data['category'],
            'amount' => $data['amount'],
            'note' => $combinedNote,
        ]);

        // Log
        try {
            \App\Models\ActivityLog::log($income, 'updated', [
                'before' => $before,
                'after' => [
                    'date' => $data['date'],
                    'category' => $data['category'],
                    'amount' => $data['amount'],
                    'note' => $combinedNote,
                    'type' => 'income',
                ],
            ]);
        } catch (\Throwable $e) { /* ignore */ }
        return redirect()->route('other-incomes.index')->with('status','Income updated');
    }

    public function destroy(Cashbook $income)
    {
        abort_unless($income->type === 'income', 404);
        $before = [
            'date' => optional($income->date)->format('Y-m-d'),
            'category' => $income->category,
            'amount' => $income->amount,
            'note' => $income->note,
        ];
        $income->delete();
        try { \App\Models\ActivityLog::log($income, 'deleted', ['before' => $before]); } catch (\Throwable $e) { }
        return redirect()->route('other-incomes.index')->with('status','Income deleted');
    }
}
