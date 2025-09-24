<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:Admin']);
    }

    public function index()
    {
        $settings = Setting::first();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'monthly_subscription_amount' => ['required','numeric','min:0'],
            'fine_amount' => ['required','numeric','min:0'],
        ]);

        $settings = Setting::first();
        if (!$settings) {
            $settings = Setting::create($data);
        } else {
            $settings->update($data);
        }

        return back()->with('status', 'Settings updated successfully');
    }

    public function tools()
    {
        $settings = Setting::first();
        return view('settings.tools', compact('settings'));
    }

    public function updateTools(Request $request)
    {
        $data = $request->validate([
            'allow_accountant_edit_deposits' => ['nullable','boolean'],
            'allow_accountant_edit_expenses' => ['nullable','boolean'],
            'allow_accountant_edit_other_income' => ['nullable','boolean'],
            'allow_accountant_edit_investment_interest' => ['nullable','boolean'],
        ]);

        // checkboxes may be missing if off; coerce to 0/1
        $payload = [
            'allow_accountant_edit_deposits' => (bool)($request->allow_accountant_edit_deposits),
            'allow_accountant_edit_expenses' => (bool)($request->allow_accountant_edit_expenses),
            'allow_accountant_edit_other_income' => (bool)($request->allow_accountant_edit_other_income),
            'allow_accountant_edit_investment_interest' => (bool)($request->allow_accountant_edit_investment_interest),
        ];

        $settings = Setting::first();
        if (!$settings) {
            $settings = Setting::create(array_merge([
                'monthly_subscription_amount' => 0,
                'fine_amount' => 0,
            ], $payload));
        } else {
            $settings->update($payload);
        }

        return back()->with('status', 'Tools settings updated');
    }
}
