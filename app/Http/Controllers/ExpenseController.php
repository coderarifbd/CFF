<?php

namespace App\Http\Controllers;

use App\Models\Cashbook;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // Admin + Accountant can manage expenses; restrict delete to Admin
        $this->middleware(['role:Admin|Accountant'])->only(['index','create','store','edit','update']);
        $this->middleware(['role:Admin'])->only(['destroy']);
    }

    public function index()
    {
        $query = Cashbook::where('type','expense')->latest('date');

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

        $expenses = $query->with('addedBy')->paginate(15)->withQueryString();

        $total = (float) (clone $query)->sum('amount');

        // Distinct categories for filter/dropdown
        $categories = Cashbook::where('type','expense')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();

        if (request()->ajax() || request('ajax')) {
            return view('expenses.partials.table', compact('expenses','total'));
        }

        // Dynamic years from expense dates
        $minDate = Cashbook::where('type','expense')->min('date');
        $maxDate = Cashbook::where('type','expense')->max('date');
        $yearStart = $minDate ? (int) date('Y', strtotime($minDate)) : 2019;
        $yearEnd = max((int) date('Y'), $maxDate ? (int) date('Y', strtotime($maxDate)) : (int) date('Y')) + 1;

        return view('expenses.index', compact('expenses','total','categories','yearStart','yearEnd'));
    }

    public function create()
    {
        $categories = Cashbook::where('type','expense')->select('category')->distinct()->orderBy('category')->pluck('category');
        return view('expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => ['required','date'],
            'category' => ['required','string','max:100'],
            'amount' => ['required','numeric','min:0'],
            'note' => ['nullable','string','max:255'],
        ]);

        Cashbook::create([
            'date' => $data['date'],
            'type' => 'expense',
            'category' => $data['category'],
            'amount' => $data['amount'],
            'note' => $data['note'] ?? null,
            'added_by' => auth()->id(),
        ]);

        return redirect()->route('expenses.index')->with('status','Expense added');
    }

    public function edit(Cashbook $expense)
    {
        abort_unless($expense->type === 'expense', 404);
        $categories = Cashbook::where('type','expense')->select('category')->distinct()->orderBy('category')->pluck('category');
        return view('expenses.edit', ['expense' => $expense, 'categories' => $categories]);
    }

    public function update(Request $request, Cashbook $expense)
    {
        abort_unless($expense->type === 'expense', 404);
        $data = $request->validate([
            'date' => ['required','date'],
            'category' => ['required','string','max:100'],
            'amount' => ['required','numeric','min:0'],
            'note' => ['nullable','string','max:255'],
        ]);

        $expense->update($data);
        return redirect()->route('expenses.index')->with('status','Expense updated');
    }

    public function destroy(Cashbook $expense)
    {
        abort_unless($expense->type === 'expense', 404);
        $expense->delete();
        return redirect()->route('expenses.index')->with('status','Expense deleted');
    }
}
