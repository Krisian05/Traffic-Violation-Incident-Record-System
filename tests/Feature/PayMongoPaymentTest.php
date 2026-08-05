<?php

namespace Tests\Feature;

use App\Models\Lgu;
use App\Models\OnlinePaymentSession;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Violator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayMongoPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Lgu $lgu;
    protected ViolationType $type;
    protected Violator $violator;
    protected Violation $violation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->lgu = Lgu::create([
            'code'                    => 'CEB',
            'name'                    => 'Balamban',
            'province'                => 'Cebu',
            'paymongo_public_key'     => 'pk_test_12345',
            'paymongo_secret_key'     => 'sk_test_12345',
            'paymongo_webhook_secret' => 'whsk_test_12345',
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
    }

    public function test_initiating_paymongo_checkout_creates_online_session(): void
    {
        Http::fake([
            'https://api.paymongo.com/v1/checkout_sessions' => Http::response([
                'data' => [
                    'id' => 'cs_test_99999',
                    'attributes' => [
                        'checkout_url' => 'https://checkout.paymongo.com/cs_test_99999',
                        'payment_intent' => [
                            'id' => 'pi_test_88888',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->post(route('guest-payment.paymongo-checkout', $this->violation->public_payment_token));

        $response->assertRedirect('https://checkout.paymongo.com/cs_test_99999');

        $this->assertEquals(1, OnlinePaymentSession::count());
        $session = OnlinePaymentSession::first();
        $this->assertEquals('cs_test_99999', $session->checkout_session_id);
        $this->assertEquals('pi_test_88888', $session->payment_intent_id);
        $this->assertEquals(1500.00, (float) $session->amount);
        $this->assertEquals('pending', $session->status);
    }

    public function test_settled_violation_cannot_initiate_paymongo_checkout(): void
    {
        $this->violation->update(['status' => 'settled']);

        $response = $this->post(route('guest-payment.paymongo-checkout', $this->violation->public_payment_token));

        $response->assertRedirect(route('guest-payment.show', $this->violation->public_payment_token));
        $this->assertEquals(0, OnlinePaymentSession::count());
    }
}
