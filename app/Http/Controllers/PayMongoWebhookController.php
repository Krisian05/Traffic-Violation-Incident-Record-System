<?php

namespace App\Http\Controllers;

use App\Models\OnlinePaymentSession;
use App\Services\PayMongoService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    public function handle(Request $request, PayMongoService $payMongoService, PaymentService $paymentService)
    {
        $rawPayload = $request->getContent();
        $signatureHeader = $request->header('Paymongo-Signature') ?? $request->header('paymongo-signature');

        $data = json_decode($rawPayload, true);

        if (!$data || !isset($data['data']['attributes']['type'])) {
            return response()->json(['error' => 'Invalid webhook payload structure.'], 400);
        }

        $eventType = $data['data']['attributes']['type'];
        $eventResource = $data['data']['attributes']['data'] ?? [];
        $resourceAttributes = $eventResource['attributes'] ?? [];

        Log::info("PayMongo Webhook received: {$eventType}", ['event_id' => $data['data']['id'] ?? null]);

        if (!in_array($eventType, ['checkout_session.payment.paid', 'payment.paid'])) {
            return response()->json(['status' => 'ignored', 'message' => "Event type {$eventType} is not handled."], 200);
        }

        // Identify checkout_session_id or payment_intent_id
        $checkoutSessionId = null;
        $paymentIntentId   = null;

        if ($eventType === 'checkout_session.payment.paid') {
            $checkoutSessionId = $eventResource['id'] ?? null;
            $paymentIntentId   = $resourceAttributes['payment_intent']['id'] ?? null;
        } else {
            // payment.paid
            $paymentIntentId = $resourceAttributes['payment_intent_id'] ?? null;
        }

        // Locate corresponding online payment session
        $query = OnlinePaymentSession::query();
        if ($checkoutSessionId) {
            $query->where('checkout_session_id', $checkoutSessionId);
        } elseif ($paymentIntentId) {
            $query->where('payment_intent_id', $paymentIntentId);
        } else {
            return response()->json(['error' => 'No session tracking ID found in webhook event.'], 400);
        }

        $session = $query->with('lgu')->first();

        if (!$session) {
            Log::warning("PayMongo Webhook: OnlinePaymentSession not found", [
                'checkout_session_id' => $checkoutSessionId,
                'payment_intent_id'   => $paymentIntentId,
            ]);
            return response()->json(['error' => 'Matching payment session not found.'], 404);
        }

        // Retrieve LGU or global webhook secret
        $webhookSecret = $session->lgu?->getPayMongoWebhookSecret();

        if ($webhookSecret && !$payMongoService->verifyWebhookSignature($rawPayload, $signatureHeader, $webhookSecret)) {
            Log::error("PayMongo Webhook: Invalid signature", [
                'session_id' => $session->id,
            ]);
            return response()->json(['error' => 'Invalid webhook signature.'], 401);
        }

        // Extract gateway details
        $payments = $resourceAttributes['payments'] ?? [];
        $latestPayment = !empty($payments) ? end($payments) : null;

        $gatewayPaymentId = $latestPayment['id'] ?? ($eventResource['id'] ?? ('PAYMONGO-' . time()));
        $paymentMethod = $latestPayment['attributes']['source']['type'] ?? ($resourceAttributes['source']['type'] ?? 'gcash');

        // Settle violation idempotently via PaymentService
        $payment = $paymentService->settleFromOnlineSession(
            $session,
            $gatewayPaymentId,
            $paymentMethod,
            $data
        );

        Log::info("PayMongo Webhook: Settlement successful", [
            'session_id'   => $session->id,
            'violation_id' => $session->violation_id,
            'or_number'    => $payment->or_number,
        ]);

        return response()->json([
            'status'     => 'success',
            'message'    => 'Payment session settled successfully.',
            'or_number'  => $payment->or_number,
        ], 200);
    }
}
