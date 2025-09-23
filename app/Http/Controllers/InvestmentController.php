<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\InvestmentInterest;
use App\Models\Cashbook;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Requests\StoreInvestmentRequest;
use App\Http\Requests\UpdateInvestmentRequest;
use App\Http\Requests\StoreInterestRequest;

class InvestmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // Admin has full control over investments
        $this->middleware(['role:Admin'])->only(['create','store','edit','update','destroy','markReturned']);
        // Admin + Accountant can view list/show and manage interests (add/edit/update)
        $this->middleware(['role:Admin|Accountant'])->only(['index','show','storeInterest','editInterest','updateInterest']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Investment::with('addedBy')->latest('date');

        if (request('type')) {
            $query->where('type', request('type'));
        }
        if (request('status')) {
            $query->where('status', request('status'));
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
                $q->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('addedBy', function($qq) use ($search) {
                      $qq->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        $investments = $query->paginate(15)->withQueryString();

        // Summary metrics for current filter
        $filteredIds = (clone $query)->pluck('id');
        $summary = [
            'count' => (clone $query)->count(),
            'activeCount' => (clone $query)->where('status','active')->count(),
            'returnedCount' => (clone $query)->where('status','returned')->count(),
            'amountSum' => (clone $query)->sum('amount'),
            'activeAmountSum' => (clone $query)->where('status','active')->sum('amount'),
            'returnedAmountSum' => (clone $query)->where('status','returned')->sum('return_amount'),
            'interestSum' => InvestmentInterest::whereIn('investment_id', $filteredIds)->sum('amount'),
        ];

        // Global total interest (all-time) for reference if needed
        $totalInterest = InvestmentInterest::sum('amount');

        // Dynamic year range from investments
        $minDate = Investment::min('date');
        $maxDate = Investment::max('date');
        $yearStart = $minDate ? (int) date('Y', strtotime($minDate)) : 2019;
        $yearEnd = max((int) date('Y'), $maxDate ? (int) date('Y', strtotime($maxDate)) : (int) date('Y')) + 1;

        if (request()->ajax() || request('ajax')) {
            return view('investments.partials.table', compact('investments','totalInterest','summary'));
        }

        return view('investments.index', compact('investments','totalInterest','summary','yearStart','yearEnd'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('investments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvestmentRequest $request)
    {
        $data = $request->validated();
        $data['added_by'] = auth()->id();

        if ($request->hasFile('agreement_document')) {
            $data['agreement_document'] = $request->file('agreement_document')->store('agreements', 'public');
        }

        $investment = Investment::create($data);

        // Create cashbook expense entry for investment outflow
        Cashbook::create([
            'date' => $investment->date,
            'type' => 'expense',
            'category' => 'Investment Outflow',
            'amount' => $investment->amount,
            'reference_type' => Investment::class,
            'reference_id' => $investment->id,
            'note' => $investment->notes,
            'added_by' => auth()->id(),
        ]);

        return redirect()->route('investments.index')->with('status', 'Investment created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Investment $investment)
    {
        $investment->load(['interests.addedBy','addedBy']);
        return view('investments.show', compact('investment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Investment $investment)
    {
        return view('investments.edit', compact('investment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvestmentRequest $request, Investment $investment)
    {
        $data = $request->validated();
        if ($request->hasFile('agreement_document')) {
            if ($investment->agreement_document) {
                Storage::disk('public')->delete($investment->agreement_document);
            }
            $data['agreement_document'] = $request->file('agreement_document')->store('agreements', 'public');
        }

        $investment->update($data);
        return redirect()->route('investments.show', $investment)->with('status', 'Investment updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Investment $investment)
    {
        // Delete related cashbook entries
        Cashbook::where('reference_type', Investment::class)
            ->where('reference_id', $investment->id)->delete();

        if ($investment->agreement_document) {
            Storage::disk('public')->delete($investment->agreement_document);
        }

        $investment->delete();
        return redirect()->route('investments.index')->with('status', 'Investment deleted');
    }

    /**
     * Store interest for an investment (Admin + Accountant)
     */
    public function storeInterest(StoreInterestRequest $request, Investment $investment)
    {
        if ($investment->status === 'returned') {
            return back()->withErrors(['amount' => 'Cannot add interest to a returned investment.']);
        }

        $interest = $investment->interests()->create([
            'date' => $request->date,
            'amount' => $request->amount,
            'note' => $request->note,
            'added_by' => auth()->id(),
        ]);

        // Cashbook income entry for interest
        Cashbook::create([
            'date' => $interest->date,
            'type' => 'income',
            'category' => 'Interest',
            'amount' => $interest->amount,
            'reference_type' => InvestmentInterest::class,
            'reference_id' => $interest->id,
            'note' => $interest->note,
            'added_by' => auth()->id(),
        ]);

        return back()->with('status','Interest added');
    }

    /**
     * Mark investment as returned (Admin only) and create cashbook entry.
     */
    public function markReturned(Request $request, Investment $investment)
    {
        $data = $request->validate([
            'return_date' => ['required','date'],
            'return_amount' => ['required','numeric','min:0'],
            'note' => ['nullable','string'],
        ]);

        $investment->update([
            'status' => 'returned',
            'return_date' => $data['return_date'],
            'return_amount' => $data['return_amount'],
        ]);

        // Cashbook income entry for return amount
        Cashbook::create([
            'date' => $data['return_date'],
            'type' => 'income',
            'category' => 'Investment Return',
            'amount' => $data['return_amount'],
            'reference_type' => Investment::class,
            'reference_id' => $investment->id,
            'note' => $data['note'] ?? null,
            'added_by' => auth()->id(),
        ]);

        return redirect()->route('investments.show', $investment)->with('status','Investment marked as returned');
    }

    /**
     * Show edit form for a specific interest (Admin + Accountant)
     */
    public function editInterest(Investment $investment, InvestmentInterest $interest)
    {
        // Ensure relationship
        abort_if($interest->investment_id !== $investment->id, 404);
        return view('investments.interest_edit', compact('investment','interest'));
    }

    /**
     * Update a specific interest and sync its cashbook record
     */
    public function updateInterest(Request $request, Investment $investment, InvestmentInterest $interest)
    {
        abort_if($interest->investment_id !== $investment->id, 404);
        $data = $request->validate([
            'date' => ['required','date'],
            'amount' => ['required','numeric','min:0'],
            'note' => ['nullable','string','max:255'],
        ]);

        $interest->update($data);

        // Update linked cashbook row
        Cashbook::where('reference_type', InvestmentInterest::class)
            ->where('reference_id', $interest->id)
            ->update([
                'date' => $data['date'],
                'amount' => $data['amount'],
                'note' => $data['note'] ?? null,
            ]);

        return redirect()->route('investments.show', $investment)->with('status','Interest updated');
    }
}
