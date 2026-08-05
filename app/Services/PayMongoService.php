<?php

namespace App\Services;

use App\Models\OnlinePaymentSession;
use App\Models\Violation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class PayMongoService
{
    /**
     * Create a PayMongo Hosted Checkout Session for a violation.
     */
    public function createCheckoutSession(Violation $violation, float $amount, string $successUrl, string $cancelUrl): OnlinePaymentSession
    {
        $lgu = $violation->lgu;
        $secretKey = $lgu?->getPayMongoSecretKey();

        if (empty($secretKey)) {
            throw new InvalidArgumentException('PayMongo integration is not configured for this municipality.');
        }

        $violation->loadMissing(['violator', 'violationType']);

        // Amount in centavos (PHP 100.00 = 10000 centavos)
        $amountInCentavos = (int) round($amount * 100);

        $violatorName = $violation->violator?->full_name ?: 'Motorist';
        $violatorPhone = $violation->violator?->phone_number ?: null;
        $ticketNum = $violation->ticket_number ?: ('#' . $violation->id);
        $violationName = $violation->violationType?->name ?: 'Traffic Citation Fine';

        $baseUrl = config('services.paymongo.base_url', 'https://api.paymongo.com/v1');

        $payload = [
            'data' => [
                'attributes' => [
                    'billing' => array_filter([
                        'name'  => $violatorName,
                        'phone' => $violatorPhone,
                    ]),
                    'send_email_receipt'   => false,
                    'show_description'    => true,
                    'show_line_items'     => true,
                    'description'         => "Traffic Violation Citation {$ticketNum}",
                    'payment_method_types' => ['gcash', 'paymaya', 'qrph', 'card', 'grab_pay'],
                    'reference_number'     => $ticketNum,
                    'success_url'          => $successUrl,
                    'cancel_url'           => $cancelUrl,
                    'line_items'           => [
                        [
                            'currency'    => 'PHP',
                            'amount'      => $amountInCentavos,
                            'name'        => "Citation Fine ({$ticketNum})",
                            'description' => "Violation fine for: {$violationName}",
                            'quantity'    => 1,
                        ],
                        [
                            'currency'    => 'PHP',
                            'amount'      => 1000, // ₱10.00 convenience fee
                            'name'        => "Online Convenience Fee",
                            'description' => "Processing fee for instant online gateway settlement",
                            'quantity'    => 1,
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::withBasicAuth($secretKey, '')
            ->acceptJson()
            ->post("{$baseUrl}/checkout_sessions", $payload);

        if ($response->failed()) {
            Log::error('PayMongo Checkout Session creation failed', [
                'status'  => $response->status(),
                'body'    => $response->json(),
                'ticket'  => $ticketNum,
            ]);

            $errorMsg = $response->json('errors.0.detail') ?? 'Unable to connect to PayMongo online payment gateway.';
            throw new RuntimeException("PayMongo API error: {$errorMsg}");
        }

        $responseData = $response->json('data');
        $sessionId    = $responseData['id'];
        $checkoutUrl  = $responseData['attributes']['checkout_url'];
        $paymentIntentId = $responseData['attributes']['payment_intent']['id'] ?? null;

        // Save session record in database
        return OnlinePaymentSession::create([
            'violation_id'        => $violation->id,
            'lgu_id'              => $violation->lgu_id,
            'checkout_session_id' => $sessionId,
            'payment_intent_id'   => $paymentIntentId,
            'payment_gateway'     => 'paymongo',
            'amount'              => round($amount, 2),
            'currency'            => 'PHP',
            'status'              => 'pending',
            'checkout_url'        => $checkoutUrl,
            'raw_payload'         => $responseData,
        ]);
    }

    /**
     * Verify the HMAC SHA256 signature of an incoming PayMongo webhook request.
     * PayMongo signature header format: "t=TIMESTAMP,te=TEST_SIGNATURE,li=LIVE_SIGNATURE"
     */
    public function verifyWebhookSignature(string $rawPayload, ?string $signatureHeader, string $webhookSecret): bool
    {
        if (empty($signatureHeader) || empty($webhookSecret)) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $item) {
            $kv = explode('=', trim($item), 2);
            if (count($kv) === 2) {
                $parts[$kv[0]] = $kv[1];
            }
        }

        $timestamp = $parts['t'] ?? null;
        $testSig   = $parts['te'] ?? null;
        $liveSig   = $parts['li'] ?? null;

        if (!$timestamp || (!$testSig && !$liveSig)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', "{$timestamp}.{$rawPayload}", $webhookSecret);

        if ($testSig && hash_equals($expectedSignature, $testSig)) {
            return true;
        }

        if ($liveSig && hash_equals($expectedSignature, $liveSig)) {
            return true;
        }

        return false;
    }
}
