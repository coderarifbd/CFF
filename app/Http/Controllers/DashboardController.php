<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DepositReceipt;
use App\Models\DepositItem;
use App\Models\Cashbook;
use App\Models\Investment;
use App\Models\InvestmentInterest;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // For Members: show own totals only; for Admin/Accountant/Super Admin: show global totals
        if (auth()->user()->hasRole('Member')) {
            $member = Member::where('user_id', auth()->id())->first();
            // Global totals for dashboard
            $totalReceipts = (float) DepositReceipt::sum('total_amount');
            $totalsByType = DepositItem::selectRaw("type, SUM(amount) as sum")
                ->groupBy('type')
                ->pluck('sum', 'type');
            $totalSubscription = (float) ($totalsByType['subscription'] ?? 0);
            $totalExtra = (float) ($totalsByType['extra'] ?? 0);
            $totalFine = (float) ($totalsByType['fine'] ?? 0);

            // Company equal share (optional): include in cashBalance for member view
            $income = (float) Cashbook::where('type','income')->sum('amount');
            $expense = (float) Cashbook::where('type','expense')->sum('amount');
            $companyBalance = $income - $expense;
            $activeMembers = max(1, (int) Member::where('status','active')->count());
            $equalShare = $companyBalance / $activeMembers;
            $cashBalance = ($totalReceipts - $totalFine) + $equalShare; // member net + share

            // Global metrics for dashboard cards (even in member view)
            $totalInvestOutflow = (float) Investment::sum('amount');
            $totalInvestReturn = (float) Investment::where('status','returned')->sum('return_amount');
            $totalInvestInterest = (float) InvestmentInterest::sum('amount');
            $activeInvestments = (int) Investment::where('status','active')->count();
            $returnedInvestments = (int) Investment::where('status','returned')->count();
            $activeInvestAmount = (float) Investment::where('status','active')->sum('amount');
            $returnedInvestAmount = (float) Investment::where('status','returned')->sum('return_amount');
            $remainingAfterInvest = $totalReceipts + $totalInvestReturn + $totalInvestInterest - $totalInvestOutflow;
            $membersCount = (int) Member::count();

            // Expenses
            $expenseAll = (float) Cashbook::where('type','expense')->sum('amount');
            $otherExpenses = (float) Cashbook::where('type','expense')
                ->where('category','!=','Investment Outflow')
                ->sum('amount');
            // Other income (non-deposit) - exclude system categories
            $systemIncomeCats = ['Subscription','Extra','Fine','Interest','Investment Return'];
            $otherIncome = (float) Cashbook::where('type','income')
                ->whereNotIn('category', $systemIncomeCats)
                ->sum('amount');

            // Reporting metrics
            $totalBalance = $totalReceipts + $totalInvestInterest; // deposits + interest (exclude returns)
            // Show ALL expenses in the KPI card
            $totalExpense = $expenseAll;
            // Remaining Balance: Total + Returns + OtherIncome − Invest − Expense (all)
            $remainingBalance = $totalBalance + $totalInvestReturn + $otherIncome - $totalInvestOutflow - $expenseAll;
            // For consistency
            $cashBalance = $remainingBalance;
            $recentReceipts = DepositReceipt::with(['member','items'])
                ->where('member_id', optional($member)->id)
                ->latest('date')->limit(5)->get();
            $recentInvestments = Investment::latest('date')->limit(5)->get();
        } else {
            // Global totals
            $totalReceipts = DepositReceipt::sum('total_amount');

            $totalsByType = DepositItem::selectRaw("type, SUM(amount) as sum")
                ->groupBy('type')
                ->pluck('sum', 'type');
            $totalSubscription = (float) ($totalsByType['subscription'] ?? 0);
            $totalExtra = (float) ($totalsByType['extra'] ?? 0);
            $totalFine = (float) ($totalsByType['fine'] ?? 0);

            // Investments
            $totalInvestOutflow = (float) Investment::sum('amount');
            $totalInvestReturn = (float) Investment::where('status','returned')->sum('return_amount');
            $totalInvestInterest = (float) InvestmentInterest::sum('amount');
            $activeInvestments = (int) Investment::where('status','active')->count();
            $returnedInvestments = (int) Investment::where('status','returned')->count();
            $activeInvestAmount = (float) Investment::where('status','active')->sum('amount');
            $returnedInvestAmount = (float) Investment::where('status','returned')->sum('return_amount');

            // Remaining balance after invest (from deposits) = deposits + returns + interest - invest outflow
            $remainingAfterInvest = $totalReceipts + $totalInvestReturn + $totalInvestInterest - $totalInvestOutflow;

            // Expenses (compute both all and excluding invest outflow)
            $expenseAll = (float) Cashbook::where('type','expense')->sum('amount');
            $otherExpenses = (float) Cashbook::where('type','expense')
                ->where('category','!=','Investment Outflow')
                ->sum('amount');
            // Other income (non-deposit) - exclude system categories (case-insensitive)
            $systemIncomeCats = ['subscription','extra','fine','interest','investment return'];
            $placeholders = implode(',', array_fill(0, count($systemIncomeCats), '?'));
            $otherIncome = (float) Cashbook::where('type','income')
                ->whereRaw('LOWER(category) NOT IN ('.$placeholders.')', $systemIncomeCats)
                ->sum('amount');

            // Reporting metrics (exclude returns from total balance)
            $totalBalance = $totalReceipts + $totalInvestInterest; // deposits + interest
            // Show ALL expenses in the KPI card
            $totalExpense = $expenseAll;
            // Remaining Balance: Total + Returns + OtherIncome − Invest − Expense (all)
            $remainingBalance = $totalBalance + $totalInvestReturn + $otherIncome - $totalInvestOutflow - $expenseAll;
            // Backward compatibility for existing blade (if referenced)
            $cashBalance = $remainingBalance;

            $membersCount = (int) Member::count();

            // Recent items
            $recentReceipts = DepositReceipt::with(['member','items'])->latest('date')->limit(5)->get();
            $recentInvestments = Investment::latest('date')->limit(5)->get();
        }

        return view('dashboard', compact(
            'totalReceipts',
            'totalSubscription',
            'totalExtra',
            'totalFine',
            'totalInvestOutflow',
            'totalInvestReturn',
            'totalInvestInterest',
            'cashBalance',
            'remainingAfterInvest',
            'membersCount',
            'activeInvestments',
            'returnedInvestments',
            'activeInvestAmount',
            'returnedInvestAmount',
            'recentReceipts',
            'recentInvestments',
            'totalBalance',
            'remainingBalance',
            'totalExpense',
            'otherIncome'
        ));
    }
}
