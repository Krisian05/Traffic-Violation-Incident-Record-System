<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Void a payment — marks it as voided and recomputes the violation status.
     * Restricted to Treasurer and Super Admin via PaymentPolicy.
     */
    public function void(Request $request, Payment $payment)
    {
        $this->authorize('void', $payment);

        $data = $request->validate([
            'void_reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            app(PaymentService::class)->voidPayment($payment, Auth::user(), $data['void_reason']);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment OR#' . $payment->or_number . ' has been voided successfully.');
    }

    /**
     * Correct a payment's method — only updates the payment_method field.
     * Restricted to Treasurer and Super Admin via PaymentPolicy.
     */
    public function correct(Request $request, Payment $payment)
    {
        $this->authorize('correct', $payment);

        $data = $request->validate([
            'payment_method' => ['required', 'in:cash,gcash,maya,bank,other'],
        ]);

        $payment->update(['payment_method' => $data['payment_method']]);

        // Also update the violation's mirrored payment_method if this is the latest active payment
        $violation = $payment->violation;
        if ($violation) {
            $latestActive = $violation->activePayments()->latest('paid_at')->first();
            if ($latestActive && $latestActive->id === $payment->id) {
                $violation->update(['payment_method' => $data['payment_method']]);
            }
        }

        return back()->with('success', 'Payment method corrected to ' . ucfirst($data['payment_method']) . '.');
    }
}
