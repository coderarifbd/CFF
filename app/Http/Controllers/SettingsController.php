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
}
