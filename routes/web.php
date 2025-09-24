<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OtherIncomeController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Members
    Route::resource('members', MemberController::class);
    Route::post('members/{member}/suspend', [MemberController::class, 'suspend'])->middleware('role:Admin')->name('members.suspend');
    Route::post('members/{member}/activate', [MemberController::class, 'activate'])->middleware('role:Admin')->name('members.activate');
    // Admin-only password reset for a member's linked user
    Route::post('members/{member}/reset-password', [MemberController::class, 'resetPassword'])
        ->middleware('role:Admin')
        ->name('members.reset-password');

    // Member self-view MUST come before resource route so it does not get captured by deposits/{id}
    Route::get('deposits/my', [DepositController::class, 'my'])->name('deposits.my');
    // Deposit helpers: keep BEFORE resource route to avoid capture by deposits/{id}
    Route::get('deposits/last-month', [DepositController::class, 'lastMonth'])->name('deposits.last-month');
    Route::get('deposits/history', [DepositController::class, 'history'])->name('deposits.history');
    // Bulk deposit (Admin + Accountant)
    Route::get('deposits/bulk-create', [DepositController::class, 'bulkCreate'])->name('deposits.bulk-create');
    Route::post('deposits/bulk-store', [DepositController::class, 'bulkStore'])->name('deposits.bulk-store');
    // Deposits (override parameter name to avoid legacy model binding)
    Route::resource('deposits', DepositController::class)->parameters(['deposits' => 'id']);

    // Settings (Admin only)
    Route::middleware('role:Admin')->group(function(){
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        // Settings > Tools (feature toggles)
        Route::get('/settings/tools', [SettingsController::class, 'tools'])->name('settings.tools');
        Route::post('/settings/tools', [SettingsController::class, 'updateTools'])->name('settings.tools.update');
        // Reports
        Route::get('/settings/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    });

    // Admin or Super Admin: Activity Logs
    Route::middleware('role:Admin|Super Admin')->group(function(){
        Route::get('/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    // Investments
    Route::resource('investments', InvestmentController::class);
    // Add interest (Admin + Accountant)
    Route::post('investments/{investment}/interest', [InvestmentController::class, 'storeInterest'])->name('investments.interest.store');
    // Edit/Update interest (Admin + Accountant)
    Route::get('investments/{investment}/interest/{interest}/edit', [InvestmentController::class, 'editInterest'])->name('investments.interest.edit');
    Route::put('investments/{investment}/interest/{interest}', [InvestmentController::class, 'updateInterest'])->name('investments.interest.update');
    // Mark as returned (Admin only)
    Route::post('investments/{investment}/return', [InvestmentController::class, 'markReturned'])->name('investments.return');

    // Expenses
    Route::resource('expenses', ExpenseController::class);

    // Other Incomes (non-investment cashbook income)
    Route::resource('other-incomes', OtherIncomeController::class)->parameters(['other-incomes' => 'income']);
});

require __DIR__.'/auth.php';
