<?php

namespace Tests\Feature;

use App\Models\Lgu;
use App\Models\OnlinePaymentSession;
use App\Models\Payment;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Violator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayMongoWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Lgu $lgu;
    protected ViolationType $type;
    protected Violator $violator;
    protected Violation $violation;
    protected OnlinePaymentSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->lgu = Lgu::create([
            'code'                    => 'CEB',
            'name'                    => 'Balamban',
            'province'                => 'Cebu',
            'paymongo_webhook_secret' => 'whsk_test_secret_key_123',
        ]);

        $this->type = ViolationType::create([
            'name'        => 'No Helmet',
            'fine_amount' => 1500.00,
        ]);

        $this->violator = Violator::create([
            'first_name'     => 'Juan',
            'last_name'      => 'Dela Cruz',
            'license_number' => 'N01-12-345678',
        ]);

        $this->violation = Violation::create([
            'lgu_id'            => $this->lgu->id,
            'violator_id'       => $this->violator->id,
            'violation_type_id' => $this->type->id,
            'ticket_number'     => 'CEB-2026-00001',
            'date_of_violation' => now()->subDays(2),
            'status'            => 'pending',
            'recorded_by'       => $this->admin->id,
        ]);

        $this->session = OnlinePaymentSession::create([
            'violation_id'        => $this->violation->id,
            'lgu_id'              => $this->lgu->id,
            'checkout_session_id' => 'cs_test_signature_99',
            'payment_intent_id'   => 'pi_test_signature_88',
            'payment_gateway'     => 'paymongo',
            'amount'              => 1500.00,
            'currency'            => 'PHP',
            'status'              => 'pending',
        ]);
    }

    public function test_valid_webhook_signature_settles_violation(): void
    {
        $payload = json_encode([
            'data' => [
                'id' => 'evt_test_123',
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'data' => [
                        'id' => 'cs_test_signature_99',
                        'attributes' => [
                            'payment_intent' => [
                                'id' => 'pi_test_signature_88',
                            ],
                            'payments' => [
                                [
                                    'id' => 'pay_test_succ_111',
                                    'attributes' => [
                                        'source' => [
                                            'type' => 'gcash',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", 'whsk_test_secret_key_123');
        $signatureHeader = "t={$timestamp},te={$signature}";

        $response = $this->call('POST', route('webhooks.paymongo'), [], [], [], [
            'HTTP_Paymongo-Signature' => $signatureHeader,
            'CONTENT_TYPE'           => 'application/json',
        ], $payload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->session->refresh();
        $this->assertEquals('paid', $this->session->status);
        $this->assertEquals('pay_test_succ_111', $this->session->gateway_payment_id);

        $this->violation->refresh();
        $this->assertEquals('settled', $this->violation->status);
        $this->assertNotNull($this->violation->or_number);

        $this->assertEquals(1, Payment::count());
        $payment = Payment::first();
        $this->assertEquals('gcash', $payment->payment_method);
        $this->assertEquals(1500.00, (float) $payment->amount_paid);
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $payload = json_encode([
            'data' => [
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                    'data' => [
                        'id' => 'cs_test_signature_99',
                    ],
                ],
            ],
        ]);

        $signatureHeader = "t=1234567,te=invalid_signature_hash";

        $response = $this->call('POST', route('webhooks.paymongo'), [], [], [], [
            'HTTP_Paymongo-Signature' => $signatureHeader,
            'CONTENT_TYPE'           => 'application/json',
        ], $payload);

        $response->assertStatus(401);
        $this->assertEquals('pending', $this->session->fresh()->status);
        $this->assertEquals('pending', $this->violation->fresh()->status);
    }
}
