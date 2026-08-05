<?php

namespace App\Http\Controllers;

use App\Models\OnlinePaymentSession;
use App\Models\Violation;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Public, no-login guest payment flow reached by scanning the QR code printed
 * on a citation. Looked up only by the violation's non-guessable UUID
 * (public_payment_token) — never by ticket_number, which is sequential.
 */
class GuestPaymentController extends Controller
{
    public function show(string $token)
    {
        $violation = Violation::with(['violator', 'violationType', 'lgu', 'vehicle', 'pendingPaymentClaim', 'activeOnlineSession'])
            ->where('public_payment_token', $token)
            ->firstOrFail();

        return view('guest-payment.show', [
            'violation' => $violation,
            'lgu'       => $violation->lgu,
        ]);
    }

    /**
     * Initiate PayMongo hosted checkout session and redirect motorist.
     */
    public function createPayMongoCheckout(Request $request, string $token, PayMongoService $payMongoService)
    {
        $violation = Violation::with(['lgu', 'violator', 'violationType'])
            ->where('public_payment_token', $token)
            ->firstOrFail();

        if ($violation->isSettled()) {
            return redirect()->route('guest-payment.show', $token)
                ->with('error', 'This citation has already been settled.');
        }

        $balance = $violation->balanceRemaining();
        if (bccomp((string) $balance, '0', 2) <= 0) {
            return redirect()->route('guest-payment.show', $token)
                ->with('error', 'This citation has no remaining balance due.');
        }

        try {
            $successUrl = route('guest-payment.checkout-status', ['token' => $token]) . '?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl  = route('guest-payment.show', $token);

            $session = $payMongoService->createCheckoutSession(
                $violation,
                $balance,
                $successUrl,
                $cancelUrl
            );

            return redirect()->away($session->checkout_url);

        } catch (\Throwable $e) {
            Log::error('PayMongo Checkout creation failed: ' . $e->getMessage());

            return redirect()->route('guest-payment.show', $token)
                ->with('error', 'Unable to launch PayMongo checkout: ' . $e->getMessage());
        }
    }

    /**
     * Motorist return status page after PayMongo checkout.
     */
    public function checkoutStatus(string $token)
    {
        $sessionRef = request('session_id');

        $violation = Violation::with(['violator', 'violationType', 'lgu', 'latestPayment'])
            ->where('public_payment_token', $token)
            ->firstOrFail();

        $session = null;
        if ($sessionRef) {
            $session = OnlinePaymentSession::where('checkout_session_id', $sessionRef)
                ->where('violation_id', $violation->id)
                ->first();
        }

        return view('guest-payment.checkout-status', [
            'violation'  => $violation,
            'session'    => $session,
            'sessionRef' => $sessionRef,
        ]);
    }

    /**
     * Polling JSON API endpoint to check if webhook has completed settlement.
     */
    public function checkSessionStatus(string $token, string $sessionRef)
    {
        $violation = Violation::where('public_payment_token', $token)->firstOrFail();

        $session = OnlinePaymentSession::where('checkout_session_id', $sessionRef)
            ->where('violation_id', $violation->id)
            ->first();

        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        $violation->refresh();

        return response()->json([
            'session_status'   => $session->status,
            'violation_status' => $violation->status,
            'is_settled'       => $violation->isSettled(),
            'or_number'        => $violation->or_number,
            'paid_at'          => $session->paid_at?->format('M d, Y g:i A'),
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
