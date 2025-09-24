<?php

namespace App\Http\Controllers;

use App\Models\Cashbook;
use App\Models\Setting;
use Illuminate\Http\Request;

class ExpenseController extends Controller
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
        $query = Cashbook::where('type','expense')
            ->where('category','!=','Investment Outflow')
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

        $expenses = $query->with('addedBy')->paginate(15)->withQueryString();

        $total = (float) (clone $query)->sum('amount');

        // Distinct categories for filter/dropdown
        $categories = Cashbook::where('type','expense')
            ->where('category','!=','Investment Outflow')
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
            'title' => ['required','string','max:150'],
            'date' => ['required','date'],
            'category' => ['required','string','max:100'],
            'amount' => ['required','numeric','min:0'],
            'note' => ['nullable','string','max:255'],
        ]);

        // Persist title in the note column, optionally appending user's note
        $combinedNote = trim($data['title'] . (isset($data['note']) && $data['note'] !== '' ? ' — '.$data['note'] : ''));

        Cashbook::create([
            'date' => $data['date'],
            'type' => 'expense',
            'category' => $data['category'],
            'amount' => $data['amount'],
            'note' => $combinedNote,
            'added_by' => auth()->id(),
        ]);

        return redirect()->route('expenses.index')->with('status','Expense added');
    }

    public function edit(Cashbook $expense)
    {
        abort_unless($expense->type === 'expense', 404);
        if (auth()->user()->hasRole('Accountant')) {
            $enabled = (bool) optional(Setting::first())->allow_accountant_edit_expenses;
            abort_unless($enabled, 403);
        }
        $categories = Cashbook::where('type','expense')->select('category')->distinct()->orderBy('category')->pluck('category');
        return view('expenses.edit', ['expense' => $expense, 'categories' => $categories]);
    }

    public function update(Request $request, Cashbook $expense)
    {
        abort_unless($expense->type === 'expense', 404);
        if (auth()->user()->hasRole('Accountant')) {
            $enabled = (bool) optional(Setting::first())->allow_accountant_edit_expenses;
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

        // Capture before values for audit
        $before = [
            'date' => optional($expense->date)->format('Y-m-d'),
            'category' => $expense->category,
            'amount' => $expense->amount,
            'note' => $expense->note,
            'type' => 'expense',
        ];

        $expense->update([
            'date' => $data['date'],
            'category' => $data['category'],
            'amount' => $data['amount'],
            'note' => $combinedNote,
        ]);

        // Log changes (Super Admin can view later)
        try {
            \App\Models\ActivityLog::log($expense, 'updated', [
                'before' => $before,
                'after' => [
                    'date' => $data['date'],
                    'category' => $data['category'],
                    'amount' => $data['amount'],
                    'note' => $combinedNote,
                    'type' => 'expense',
                ],
            ]);
        } catch (\Throwable $e) { /* ignore logging failure */ }
        return redirect()->route('expenses.index')->with('status','Expense updated');
    }

    public function destroy(Cashbook $expense)
    {
        abort_unless($expense->type === 'expense', 404);
        // Snapshot before delete
        $before = [
            'date' => optional($expense->date)->format('Y-m-d'),
            'category' => $expense->category,
            'amount' => $expense->amount,
            'note' => $expense->note,
        ];
        $expense->delete();
        // Log deletion
        try { \App\Models\ActivityLog::log($expense, 'deleted', ['before' => $before]); } catch (\Throwable $e) { }
        return redirect()->route('expenses.index')->with('status','Expense deleted');
    }
}
