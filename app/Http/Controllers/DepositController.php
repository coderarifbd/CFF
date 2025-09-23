<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\DepositReceipt;
use App\Models\DepositItem;
use App\Models\Member;
use App\Models\Cashbook;
use App\Models\Setting;
use Illuminate\Http\Request;
// use App\Http\Requests\StoreDepositRequest; // switched to dynamic validation in controller to support multi-type submission
use App\Http\Requests\UpdateDepositRequest;

class DepositController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // Admin + Accountant can create/store
        $this->middleware(['role:Admin|Accountant'])->only(['create','store','index','show','bulkCreate','bulkStore']);
        // Only Admin can edit/update/destroy
        $this->middleware(['role:Admin'])->only(['edit','update','destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = DepositReceipt::with(['member','addedBy','items'])->latest('date');

        if (request('member_id')) {
            $query->where('member_id', request('member_id'));
        }
        if (request('type')) {
            $type = request('type');
            $query->whereHas('items', function($q) use ($type) { $q->where('type', $type); });
        }
        if (request('month') && request('year')) {
            $query->whereYear('date', request('year'))
                  ->whereMonth('date', request('month'));
        }
        if (request('search')) {
            $search = trim(request('search'));
            $query->whereHas('member', function($q) use ($search) {
                $q->where('name','like',"%{$search}%");
            });
        }

        $receipts = $query->paginate(15)->withQueryString();
        // Dynamic year range from existing data
        $minDate = DepositReceipt::min('date');
        $maxDate = DepositReceipt::max('date');
        $yearStart = $minDate ? (int) date('Y', strtotime($minDate)) : 2019;
        $yearEnd = max((int) date('Y'), $maxDate ? (int) date('Y', strtotime($maxDate)) : (int) date('Y')) + 1; // allow next year
        // Exclude suspended members from selection lists
        $members = Member::where('status','!=','suspended')->orderBy('name')->get(['id','name']);

        $totalAmount = (clone $query)->sum('total_amount');
        $fineTotal = (clone $query)->cloneWithout(['orders'])->whereHas('items', fn($q)=>$q->where('type','fine'))->withSum(['items as fine_sum' => fn($q)=>$q->where('type','fine')],'amount')->get()->sum('fine_sum');
        // fallback computation if DB adapter struggles
        if ($fineTotal === 0) {
            $fineTotal = (clone $query)->get()->flatMap->items->where('type','fine')->sum('amount');
        }

        $settings = Setting::first();
        $subscriptionAmount = optional($settings)->monthly_subscription_amount ?? 0;
        $fineAmount = optional($settings)->fine_amount ?? 0;

        return view('deposits.index', [
            'receipts' => $receipts,
            'members' => $members,
            'totalAmount' => $totalAmount,
            'fineTotal' => $fineTotal,
            'subscriptionAmount' => $subscriptionAmount,
            'fineAmount' => $fineAmount,
            'yearStart' => $yearStart,
            'yearEnd' => $yearEnd,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Exclude suspended members from selection
        $members = Member::where('status','!=','suspended')->orderBy('name')->get(['id','name']);
        $minDate = DepositReceipt::min('date');
        $maxDate = DepositReceipt::max('date');
        $yearStart = $minDate ? (int) date('Y', strtotime($minDate)) : 2019;
        $yearEnd = max((int) date('Y'), $maxDate ? (int) date('Y', strtotime($maxDate)) : (int) date('Y')) + 1;
        return view('deposits.create', compact('members','yearStart','yearEnd'));
    }

    /**
     * Bulk create deposits for multiple members with the same date/type/amount.
     * (Admin + Accountant)
     */
    public function bulkCreate()
    {
        // Exclude suspended members from selection
        $members = Member::where('status','!=','suspended')->orderBy('name')->get(['id','name']);
        // Year range (optional helper for UI hint)
        $minDate = DepositReceipt::min('date');
        $maxDate = DepositReceipt::max('date');
        $yearStart = $minDate ? (int) date('Y', strtotime($minDate)) : 2019;
        $yearEnd = max((int) date('Y'), $maxDate ? (int) date('Y', strtotime($maxDate)) : (int) date('Y')) + 1;
        return view('deposits.bulk_create', compact('members','yearStart','yearEnd'));
    }

    /**
     * Persist bulk deposits.
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required','date'],
            'payment_method' => ['required','in:cash,bank,mobile'],
            'type' => ['required','in:subscription,extra,fine'],
            'amount' => ['required','numeric','min:0.01'],
            'member_ids' => ['required','array','min:1'],
            'member_ids.*' => ['exists:members,id'],
            'note' => ['nullable','string','max:255'],
        ]);

        $created = 0; $skipped = [];
        foreach ($validated['member_ids'] as $memberId) {
            // Prevent duplicate subscription for the month
            if ($validated['type'] === 'subscription') {
                $exists = DepositReceipt::where('member_id', $memberId)
                    ->whereYear('date', date('Y', strtotime($validated['date'])))
                    ->whereMonth('date', date('m', strtotime($validated['date'])))
                    ->whereHas('items', fn($q)=>$q->where('type','subscription'))
                    ->exists();
                if ($exists) { $skipped[] = (int)$memberId; continue; }
            }

            $receipt = DepositReceipt::create([
                'date' => $validated['date'],
                'member_id' => $memberId,
                'total_amount' => (float)$validated['amount'],
                'payment_method' => $validated['payment_method'],
                'note' => $validated['note'] ?? null,
                'added_by' => auth()->id(),
            ]);

            $receipt->items()->create([
                'type' => $validated['type'],
                'amount' => (float)$validated['amount'],
            ]);

            Cashbook::create([
                'date' => $receipt->date,
                'type' => 'income',
                'category' => match($validated['type']){
                    'subscription' => 'Subscription',
                    'extra' => 'Extra',
                    'fine' => 'Fine',
                    default => 'Other',
                },
                'amount' => (float)$validated['amount'],
                'reference_type' => DepositReceipt::class,
                'reference_id' => $receipt->id,
                'note' => $receipt->note,
                'added_by' => auth()->id(),
            ]);

            $created++;
        }

        $msg = "$created deposits created.";
        if (!empty($skipped)) {
            $msg .= ' Skipped for member IDs: '.implode(',', $skipped).' (duplicate subscription in same month).';
        }
        return redirect()->route('deposits.index')->with('status', $msg);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Dynamic validation for multi-type submission via modal checkboxes
        $validated = $request->validate([
            'date' => ['required','date'],
            'member_id' => ['required','exists:members,id'],
            'payment_method' => ['required','in:cash,bank,mobile'],
            'note' => ['nullable','string'],
            'types' => ['required','array','min:1'],
            'types.*' => ['in:subscription,extra,fine'],
            'amount_subscription' => ['nullable','numeric','min:0.01'],
            'amount_extra' => ['nullable','numeric','min:0.01'],
            'amount_fine' => ['nullable','numeric','min:0.01'],
        ]);

        $types = collect($validated['types']);
        $date = $validated['date'];
        $memberId = $validated['member_id'];
        $method = $validated['payment_method'];
        $note = $validated['note'] ?? null;

        // Ensure required amount present for each selected type
        $missingAmount = [];
        foreach (['subscription','extra','fine'] as $t) {
            if ($types->contains($t)) {
                $key = 'amount_' . $t;
                if (empty($validated[$key])) {
                    $missingAmount[] = ucfirst($t) . ' amount is required';
                }
            }
        }
        if (!empty($missingAmount)) {
            return back()->withErrors(['amount' => implode('\n', $missingAmount)])->withInput();
        }

        // Prevent duplicate for subscription only
        if ($types->contains('subscription')) {
            $exists = DepositReceipt::where('member_id', $memberId)
                ->whereYear('date', date('Y', strtotime($date)))
                ->whereMonth('date', date('m', strtotime($date)))
                ->whereHas('items', function($q){ $q->where('type','subscription'); })
                ->exists();
            if ($exists) {
                return back()->withErrors(['date' => 'Subscription already recorded for this member and month.'])->withInput();
            }
        }

        // Create one receipt + items
        $items = $types->map(function($t) use ($validated){
            return [
                'type' => $t,
                'amount' => (float) $validated['amount_'.$t],
            ];
        });
        $total = $items->sum('amount');

        $receipt = DepositReceipt::create([
            'date' => $date,
            'member_id' => $memberId,
            'total_amount' => $total,
            'payment_method' => $method,
            'note' => $note,
            'added_by' => auth()->id(),
        ]);

        foreach ($items as $it) {
            $item = $receipt->items()->create($it);

            // Cashbook per item
            Cashbook::create([
                'date' => $receipt->date,
                'type' => 'income',
                'category' => match($it['type']){
                    'subscription' => 'Subscription',
                    'extra' => 'Extra',
                    'fine' => 'Fine',
                    default => 'Other',
                },
                'amount' => $it['amount'],
                'reference_type' => DepositReceipt::class,
                'reference_id' => $receipt->id,
                'note' => $note,
                'added_by' => auth()->id(),
            ]);
        }

        return redirect()->route('deposits.index')->with('status', 'Deposit saved');
    }

    /**
     * Display the specified resource.
     */
    public function show(Deposit $deposit)
    {
        return view('deposits.show', compact('deposit'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $receipt = DepositReceipt::with('items')->findOrFail($id);
        // Exclude suspended members from selection
        $members = Member::where('status','!=','suspended')->orderBy('name')->get(['id','name']);
        $settings = Setting::first();
        return view('deposits.receipt_edit', [
            'receipt' => $receipt,
            'members' => $members,
            'subscriptionAmount' => optional($settings)->monthly_subscription_amount ?? 0,
            'fineAmount' => optional($settings)->fine_amount ?? 0,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $receipt = DepositReceipt::with('items')->findOrFail($id);

        $validated = $request->validate([
            'date' => ['required','date'],
            'member_id' => ['required','exists:members,id'],
            'payment_method' => ['required','in:cash,bank,mobile'],
            'note' => ['nullable','string'],
            'types' => ['required','array','min:1'],
            'types.*' => ['in:subscription,extra,fine'],
            'amount_subscription' => ['nullable','numeric','min:0.01'],
            'amount_extra' => ['nullable','numeric','min:0.01'],
            'amount_fine' => ['nullable','numeric','min:0.01'],
        ]);

        $types = collect($validated['types']);
        $missing = [];
        foreach(['subscription','extra','fine'] as $t){ if($types->contains($t) && empty($validated['amount_'.$t])) $missing[] = ucfirst($t).' amount is required'; }
        if($missing){ return back()->withErrors(['amount'=>implode("\n",$missing)])->withInput(); }

        // subscription duplicate check excluding this receipt
        if ($types->contains('subscription')) {
            $dup = DepositReceipt::where('member_id', $validated['member_id'])
                ->whereYear('date', date('Y', strtotime($validated['date'])))
                ->whereMonth('date', date('m', strtotime($validated['date'])))
                ->where('id','!=',$receipt->id)
                ->whereHas('items', fn($q)=>$q->where('type','subscription'))
                ->exists();
            if ($dup) return back()->withErrors(['date'=>'Subscription already recorded for this member and month.'])->withInput();
        }

        // Rebuild items
        $items = $types->map(fn($t)=>['type'=>$t,'amount'=>(float)$validated['amount_'.$t]]);
        $total = $items->sum('amount');

        // Snapshot before
        $before = [
            'date' => optional($receipt->date)->format('Y-m-d'),
            'member_id' => $receipt->member_id,
            'payment_method' => $receipt->payment_method,
            'note' => $receipt->note,
            'total_amount' => $receipt->total_amount,
        ];

        $receipt->update([
            'date'=>$validated['date'],
            'member_id'=>$validated['member_id'],
            'payment_method'=>$validated['payment_method'],
            'note'=>$validated['note'] ?? null,
            'total_amount'=>$total,
        ]);

        // Remove prior cashbook entries for this receipt
        Cashbook::where('reference_type', DepositReceipt::class)->where('reference_id', $receipt->id)->delete();
        // Replace items
        $receipt->items()->delete();
        foreach($items as $it){
            $receipt->items()->create($it);
            Cashbook::create([
                'date'=>$receipt->date,
                'type'=>'income',
                'category'=>match($it['type']){
                    'subscription'=>'Subscription',
                    'extra'=>'Extra',
                    'fine'=>'Fine',
                    default=>'Other'
                },
                'amount'=>$it['amount'],
                'reference_type'=>DepositReceipt::class,
                'reference_id'=>$receipt->id,
                'note'=>$receipt->note,
                'added_by'=>auth()->id(),
            ]);
        }

        // Log activity (Super Admin can review)
        try {
            \App\Models\ActivityLog::log($receipt, 'updated', [
                'before' => $before,
                'after' => [
                    'date' => $validated['date'],
                    'member_id' => $validated['member_id'],
                    'payment_method' => $validated['payment_method'],
                    'note' => $validated['note'] ?? null,
                    'total_amount' => $total,
                ],
            ]);
        } catch (\Throwable $e) { /* swallow */ }

        return redirect()->route('deposits.index')->with('status','Deposit updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $receipt = DepositReceipt::findOrFail($id);
        $before = [
            'date' => optional($receipt->date)->format('Y-m-d'),
            'member_id' => $receipt->member_id,
            'payment_method' => $receipt->payment_method,
            'note' => $receipt->note,
            'total_amount' => $receipt->total_amount,
        ];
        Cashbook::where('reference_type', DepositReceipt::class)
            ->where('reference_id', $receipt->id)->delete();
        $receipt->delete();
        try { \App\Models\ActivityLog::log($receipt, 'deleted', ['before' => $before]); } catch (\Throwable $e) { }
        return redirect()->route('deposits.index')->with('status','Deposit deleted');
    }

    /**
     * Member self view: own deposit history and balance/share.
     */
    public function my()
    {
        $member = Member::where('user_id', auth()->id())->firstOrFail();

        $query = DepositReceipt::with(['items','addedBy'])
            ->where('member_id', $member->id)
            ->latest('date');

        if (request('month') && request('year')) {
            $query->whereYear('date', request('year'))
                  ->whereMonth('date', request('month'));
        }

        $receipts = $query->paginate(15)->withQueryString();

        // Per-member totals
        $subscriptionSum = DepositItem::whereHas('receipt', fn($q)=>$q->where('member_id',$member->id))
            ->where('type','subscription')->sum('amount');
        $extraSum = DepositItem::whereHas('receipt', fn($q)=>$q->where('member_id',$member->id))
            ->where('type','extra')->sum('amount');
        $fineSum = DepositItem::whereHas('receipt', fn($q)=>$q->where('member_id',$member->id))
            ->where('type','fine')->sum('amount');
        $myDeposit = (float)$subscriptionSum + (float)$extraSum;
        $myNet = $myDeposit - (float)$fineSum;

        // Company balance and equal share (per your rule: use total deposits only)
        $companyBalance = (float) DepositReceipt::sum('total_amount');
        $activeMembers = max(1, (int) Member::where('status','active')->count());
        $equalShare = $companyBalance / $activeMembers;

        // As per rule: after expenses, total company balance is divided equally. So member's balance incl. share = equalShare
        $myWithShare = $equalShare;

        // Year range for filters (align with index/create)
        $minDate = DepositReceipt::min('date');
        $maxDate = DepositReceipt::max('date');
        $yearStart = $minDate ? (int) date('Y', strtotime($minDate)) : 2019;
        $yearEnd = max((int) date('Y'), $maxDate ? (int) date('Y', strtotime($maxDate)) : (int) date('Y')) + 1;

        return view('deposits.my', compact(
            'member','receipts','subscriptionSum','extraSum','fineSum','myDeposit','myNet','companyBalance','activeMembers','equalShare','yearStart','yearEnd'
        ));
    }

    /**
     * Helper endpoint: last month's payment details for a member.
     * GET /deposits/last-month?member_id=ID[&month=MM&year=YYYY]
     */
    public function lastMonth(Request $request)
    {
        try {
            $request->validate([
                'member_id' => ['required','exists:members,id'],
                'month' => ['nullable','integer','between:1,12'],
                'year' => ['nullable','integer','min:2000','max:2100'],
            ]);

            $memberId = (int) $request->member_id;
            // Default to previous calendar month
            $ref = now()->startOfMonth()->subMonth();
            $month = (int) ($request->month ?? (int)$ref->format('m'));
            $year  = (int) ($request->year  ?? (int)$ref->format('Y'));

            // Pull items joined to receipts for the given month/year
            $items = DepositItem::whereHas('receipt', function($q) use ($memberId, $month, $year){
                    $q->where('member_id', $memberId)
                      ->whereYear('date', $year)
                      ->whereMonth('date', $month);
                })
                ->get(['type','amount']);

            $sumBy = fn($type) => (float) $items->where('type',$type)->sum('amount');
            $data = [
                'member_id' => $memberId,
                'year' => $year,
                'month' => $month,
                'subscription' => $sumBy('subscription'),
                'extra' => $sumBy('extra'),
                'fine' => $sumBy('fine'),
                'total' => (float) $items->sum('amount'),
            ];

            return response()->json($data);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => config('app.debug') ? $e->getTraceAsString() : null], 500);
        }
    }

    /**
     * Helper endpoint: last N months history for carousel (subscription presence per month).
     * GET /deposits/history?member_id=ID[&months=12&end_month=MM&end_year=YYYY]
     */
    public function history(Request $request)
    {
        try {
            $request->validate([
                'member_id' => ['required','exists:members,id'],
                'months' => ['nullable','integer','min:1','max:24'],
                'end_month' => ['nullable','integer','between:1,12'],
                'end_year' => ['nullable','integer','min:2000','max:2100'],
            ]);

            $memberId = (int) $request->member_id;
            $months = (int) ($request->months ?? 12);
            $now = now();
            $endRef = $request->filled('end_month') && $request->filled('end_year')
                ? now()->setDate((int)$request->end_year, (int)$request->end_month, 1)
                : $now;

            // Build an array of months from endRef going back $months
            $periods = [];
            for ($i = 0; $i < $months; $i++) {
                $dt = $endRef->copy()->startOfMonth()->subMonths($i);
                $periods[] = ['year' => (int)$dt->format('Y'), 'month' => (int)$dt->format('m')];
            }

            $result = [];
            foreach ($periods as $p) {
                // Pull items for this member and month
                $items = DepositItem::whereHas('receipt', function($q) use ($memberId, $p){
                        $q->where('member_id', $memberId)
                          ->whereYear('date', $p['year'])
                          ->whereMonth('date', $p['month']);
                    })
                    ->get(['type','amount']);

                $subscription = (float) $items->where('type','subscription')->sum('amount');
                $extra = (float) $items->where('type','extra')->sum('amount');
                $fine = (float) $items->where('type','fine')->sum('amount');
                $total = (float) $items->sum('amount');

                $result[] = [
                    'year' => $p['year'],
                    'month' => $p['month'],
                    'label' => sprintf('%d-%02d', $p['year'], $p['month']),
                    'subscription' => $subscription,
                    'extra' => $extra,
                    'fine' => $fine,
                    'total' => $total,
                    'due' => $subscription <= 0,
                ];
            }

            return response()->json(['items' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage(), 'trace' => config('app.debug') ? $e->getTraceAsString() : null], 500);
        }
    }
}
