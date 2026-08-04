<?php

namespace App\Http\Controllers;

use App\Models\Violation;
use Illuminate\Http\Request;

/**
 * Public, no-login guest payment flow reached by scanning the QR code printed
 * on a citation. Looked up only by the violation's non-guessable UUID
 * (public_payment_token) — never by ticket_number, which is sequential and
 * would let anyone enumerate other people's name/plate/violation/amount.
 *
 * There is no payment-gateway API here: the LGU has no merchant account, so
 * nothing on this page can prove money moved. It shows the LGU's real static
 * GCash QR and lets the violator submit a claimed reference number, which a
 * staff cashier/treasurer must manually verify against the actual GCash
 * transaction before anything is marked paid (see PaymentClaimController).
 */
class GuestPaymentController extends Controller
{
    public function show(string $token)
    {
        $violation = Violation::with(['violator', 'violationType', 'lgu', 'vehicle', 'pendingPaymentClaim'])
            ->where('public_payment_token', $token)
            ->firstOrFail();

        return view('guest-payment.show', [
            'violation' => $violation,
            'lgu'       => $violation->lgu,
        ]);
    }

    public function submitClaim(Request $request, string $token)
    {
        $violation = Violation::where('public_payment_token', $token)->firstOrFail();

        if ($violation->isSettled()) {
            return redirect()->route('guest-payment.show', $token)
                ->with('error', 'This citation has already been settled.');
        }

        if ($violation->pendingPaymentClaim()->exists()) {
            return redirect()->route('guest-payment.claim-status', $token)
                ->with('error', 'A payment claim for this citation is already awaiting verification.');
        }

        $balance = $violation->balanceRemaining();

        $data = $request->validate([
            'claimed_reference' => ['required', 'string', 'max:100'],
            'claimed_amount'    => ['required', 'numeric', 'min:0.01', 'max:' . max($balance, 0.01)],
            'claimant_name'     => ['nullable', 'string', 'max:150'],
            'claimant_contact'  => ['nullable', 'string', 'max:30'],
            'screenshot'        => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:4096'],
        ]);

        if ($request->hasFile('screenshot')) {
            $data['screenshot_path'] = $request->file('screenshot')->store('payment-claim-screenshots', uploads_disk());
        }
        unset($data['screenshot']);

        $violation->paymentClaims()->create([
            'payment_method'    => 'gcash',
            'claimed_reference' => $data['claimed_reference'],
            'claimed_amount'    => $data['claimed_amount'],
            'claimant_name'     => $data['claimant_name'] ?? null,
            'claimant_contact'  => $data['claimant_contact'] ?? null,
            'screenshot_path'   => $data['screenshot_path'] ?? null,
            'status'            => 'pending_review',
        ]);

        return redirect()->route('guest-payment.claim-status', $token)
            ->with('success', 'Your payment claim has been submitted and is awaiting staff verification.');
    }

    public function claimStatus(string $token)
    {
        $violation = Violation::with(['violator', 'violationType', 'pendingPaymentClaim'])
            ->where('public_payment_token', $token)
            ->firstOrFail();

        return view('guest-payment.claim-status', [
            'violation' => $violation,
        ]);
    }
}
