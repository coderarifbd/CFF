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
            // Member-specific totals for dashboard
            $memberId = optional($member)->id;
            $totalReceipts = (float) DepositReceipt::where('member_id', $memberId)->sum('total_amount');
            $totalsByType = DepositItem::selectRaw("type, SUM(amount) as sum")
                ->whereHas('receipt', function($q) use ($memberId) {
                    $q->where('member_id', $memberId);
                })
                ->groupBy('type')
                ->pluck('sum', 'type');
            // Keep variable names consistent with blade
            $totalSubscription = (float) ($totalsByType['subscription'] ?? 0);
            $totalExtra = (float) ($totalsByType['extra'] ?? 0);
            $totalFine = (float) ($totalsByType['fine'] ?? 0);

            // Own deposit excludes fines
            $ownDeposit = $totalSubscription + $totalExtra;

            // Global metrics for dashboard cards (even in member view)
            $totalInvestOutflow = (float) Investment::sum('amount');
            $totalInvestReturn = (float) Investment::where('status','returned')->sum('return_amount');
            $totalInvestInterest = (float) InvestmentInterest::sum('amount');
            $activeInvestments = (int) Investment::where('status','active')->count();
            $returnedInvestments = (int) Investment::where('status','returned')->count();
            $activeInvestAmount = (float) Investment::where('status','active')->sum('amount');
            $returnedInvestAmount = (float) Investment::where('status','returned')->sum('return_amount');
            $remainingAfterInvest = $totalReceipts + $totalInvestReturn + $totalInvestInterest - $totalInvestOutflow;
            // Only active members should be counted in admin dashboard as well
            $membersCount = (int) Member::where('status','active')->count();

            // GLOBALS needed for per-member shares and remaining balance
            $globalTotalReceipts      = (float) DepositReceipt::sum('total_amount');
            $globalInvestOutflow      = (float) Investment::sum('amount');
            $globalInvestReturn       = (float) Investment::where('status','returned')->sum('return_amount');
            $globalInvestInterest     = (float) InvestmentInterest::sum('amount');
            $globalOtherExpenses      = (float) Cashbook::where('type','expense')
                ->where('category','!=','Investment Outflow')->sum('amount');
            // Other income excludes system categories (case-insensitive) like in admin branch
            $systemIncomeCats = ['subscription','extra','fine','interest','investment return'];
            $placeholders = implode(',', array_fill(0, count($systemIncomeCats), '?'));
            $globalOtherIncome = (float) Cashbook::where('type','income')
                ->whereRaw('LOWER(category) NOT IN ('.$placeholders.')', $systemIncomeCats)
                ->sum('amount');

            // Reclassify suspended members' deposits as Other Income for member dashboard view
            $suspendedDeposits = (float) DepositReceipt::whereIn('member_id', function($q){
                $q->select('id')->from('members')->where('status','suspended');
            })->sum('total_amount');
            // Adjusted other income used for member dashboard KPIs and shares
            $otherIncome = $globalOtherIncome + $suspendedDeposits;

            // Only active members should be counted
            $membersCount = max(1, (int) Member::where('status','active')->count());
            // Per requirements:
            // total balance = total deposit + (interest / member count) + (other income / member count)
            // Use adjusted otherIncome that includes suspended deposits
            $totalBalance = $totalReceipts + ($globalInvestInterest / $membersCount) + ($otherIncome / $membersCount);
            // grand total = total balance - (total expense / member count)
            $totalExpense = $globalOtherExpenses;
            $grandTotal = $totalBalance - ($totalExpense / $membersCount);
            // remaining balance = company remaining balance (same as admin dashboard)
            // Avoid double counting: subtract suspended deposits from receipts and include them via otherIncome
            $adminLikeTotalBalance = ($globalTotalReceipts - $suspendedDeposits) + $globalInvestInterest + $otherIncome;
            $remainingBalance = $adminLikeTotalBalance - $totalExpense - $activeInvestAmount;
            $cashBalance = $remainingBalance;
            // For KPI card consistency in blade: $otherIncome already set above
            $recentReceipts = DepositReceipt::with(['member','items'])
                ->where('member_id', $memberId)
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

            // Reporting metrics (exclude returns from total balance for display)
            // Total Balance = Deposits + Interest + Other Income
            $totalBalance = $totalReceipts + $totalInvestInterest + $otherIncome;
            // Keep operational expense for the KPI card
            $totalExpense = $otherExpenses;
            // Reverted: Grand Total = Total Balance − Expense (operational only)
            $grandTotal = $totalBalance - $totalExpense;
            // New rule: Remaining = Total Balance − Expense − Active Investment
            $remainingBalance = $totalBalance - $totalExpense - $activeInvestAmount;
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
            'otherIncome',
            'grandTotal'
        ));
    }
}
