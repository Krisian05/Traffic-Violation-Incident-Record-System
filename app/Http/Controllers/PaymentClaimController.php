<?php

namespace App\Http\Controllers;

use App\Models\PaymentClaim;
use App\Models\Violation;
use App\Services\PaymentService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Staff-side verification queue for online GCash payment claims submitted via
 * the guest payment page. A cashier/treasurer must manually cross-check the
 * claimed reference number against the LGU's real GCash transaction history
 * before verify() runs — nothing here can be triggered by the anonymous
 * violator, unlike the removed self-service "Confirm Money Received" flow.
 */
class PaymentClaimController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = PaymentClaim::with(['violation.violator', 'violation.violationType'])
            ->where('status', 'pending_review')
            ->latest();

        if ($user->lgu_id && !$user->isSuperAdmin()) {
            $query->whereHas('violation', fn ($q) => $q->where('lgu_id', $user->lgu_id));
        }

        $claims = $query->paginate(15);

        return view('payment-claims.index', compact('claims'));
    }

    public function verify(PaymentClaim $paymentClaim)
    {
        $violation = $paymentClaim->violation;
        $this->authorize('settle', $violation);

        if (!$paymentClaim->isPendingReview()) {
            return back()->with('error', 'This claim has already been reviewed.');
        }

        if ($violation->isSettled()) {
            $paymentClaim->update([
                'status'       => 'rejected',
                'reviewed_by'  => Auth::id(),
                'reviewed_at'  => now(),
                'review_notes' => 'Citation was already settled by another payment before this claim was reviewed.',
            ]);

            return back()->with('error', 'This citation was already settled — claim marked rejected.');
        }

        $payment = app(PaymentService::class)->recordPayment($violation, [
            'or_number'      => $violation->suggestOrNumber(),
            'cashier_name'   => Auth::user()->name . ' (Online GCash — Ref ' . $paymentClaim->claimed_reference . ')',
            'payment_method' => 'gcash',
            'amount_paid'    => $paymentClaim->claimed_amount,
            'paid_at'        => now(),
        ], Auth::user());

        $paymentClaim->update([
            'status'      => 'verified',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'payment_id'  => $payment->id,
        ]);

        app(SmsService::class)->sendPaymentConfirmationSms($violation, $payment);

        return back()->with('success', 'Payment verified. Citation ' . ($violation->ticket_number ?: '#' . $violation->id) . ' marked as settled.');
    }

    public function reject(Request $request, PaymentClaim $paymentClaim)
    {
        $this->authorize('settle', $paymentClaim->violation);

        if (!$paymentClaim->isPendingReview()) {
            return back()->with('error', 'This claim has already been reviewed.');
        }

        $data = $request->validate([
            'review_notes' => ['required', 'string', 'max:500'],
        ]);

        $paymentClaim->update([
            'status'       => 'rejected',
            'reviewed_by'  => Auth::id(),
            'reviewed_at'  => now(),
            'review_notes' => $data['review_notes'],
        ]);

        return back()->with('success', 'Claim rejected.');
    }
}
