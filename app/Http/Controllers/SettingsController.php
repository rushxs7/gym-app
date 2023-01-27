<?php

namespace App\Http\Controllers;

use App\Settings\AutomationSettings;
use App\Settings\PaymentSettings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request,
        PaymentSettings $paymentSettings,
        AutomationSettings $automationSettings
    )
    {
        // dd($automationSettings);
        return view('settings', ['settings' => [
            'payments.signup_fee' => $paymentSettings->signup_fee,
            'payments.prolongation_fee' => $paymentSettings->prolongation_fee,
            'payments.penalty_fee' => $paymentSettings->penalty_fee,
            'payments.daytraining_fee' => $paymentSettings->daytraining_fee,
            'automation.days_after_expiration_before_fine' => $automationSettings->days_after_expiration_before_fine,
            'automation.closing_time' => $automationSettings->closing_time,
        ]]);
    }

    public function update($group, Request $request,
        PaymentSettings $paymentSettings,
        AutomationSettings $automationSettings
    )
    {
        switch ($group) {
            case 'payments':
                $request->validate([
                    'signup_fee' => 'required',
                    'prolongation_fee' => 'required',
                    'penalty_fee' => 'required',
                    'daytraining_fee' => 'required'
                ]);

                $paymentSettings->signup_fee = $request->signup_fee;
                $paymentSettings->prolongation_fee = $request->prolongation_fee;
                $paymentSettings->penalty_fee = $request->penalty_fee;
                $paymentSettings->daytraining_fee = $request->daytraining_fee;

                $paymentSettings->save();

                return redirect()->back()->with('success', 'Payment settings saved successfully.');
                break;

            case 'automation':
                $request->validate([
                    'days_after_expiration_before_fine' => 'required|integer',
                    'closing_time' => 'required|integer',
                ]);

                $automationSettings->days_after_expiration_before_fine = $request->days_after_expiration_before_fine;
                $automationSettings->closing_time = $request->closing_time;

                $automationSettings->save();

                return redirect()->back()->with('success', 'Automation settings saved successfully.');
                break;

            default:
                return redirect()->back()->withErrors(['msg' => 'Unknown settings group.']);
                break;
        }
    }
}
