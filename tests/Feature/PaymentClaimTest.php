<?php

namespace Tests\Feature;

use App\Models\Lgu;
use App\Models\Payment;
use App\Models\PaymentClaim;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Violator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentClaimTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected Lgu $lgu;
    protected ViolationType $type;
    protected Violator $violator;
    protected Violation $violation;
    protected PaymentClaim $claim;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->lgu = Lgu::create(['code' => 'CEB', 'name' => 'Balamban', 'province' => 'Cebu']);
        $this->cashier = User::factory()->cashier()->create(['lgu_id' => $this->lgu->id]);

        $this->type = ViolationType::create(['name' => 'No Helmet', 'fine_amount' => 1500.00]);
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

        $this->claim = $this->violation->paymentClaims()->create([
            'payment_method'    => 'gcash',
            'claimed_reference' => 'GC-REF-123456',
            'claimed_amount'    => 1500.00,
            'status'            => 'pending_review',
        ]);
    }

    public function test_cashier_can_verify_a_claim_and_settle_the_violation(): void
    {
        $response = $this->actingAs($this->cashier)
            ->post(route('payment-claims.verify', $this->claim));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(1, Payment::count());
        $payment = Payment::first();
        $this->assertEquals('gcash', $payment->payment_method);
        $this->assertEquals(1500.00, (float) $payment->amount_paid);
        $this->assertEquals($this->cashier->id, $payment->collected_by);

        $this->violation->refresh();
        $this->assertEquals('settled', $this->violation->status);

        $this->claim->refresh();
        $this->assertEquals('verified', $this->claim->status);
        $this->assertEquals($this->cashier->id, $this->claim->reviewed_by);
        $this->assertEquals($payment->id, $this->claim->payment_id);
    }

    public function test_verifying_a_claim_twice_does_not_create_a_second_payment(): void
    {
        $this->actingAs($this->cashier)->post(route('payment-claims.verify', $this->claim));
        $this->actingAs($this->cashier)->post(route('payment-claims.verify', $this->claim));

        $this->assertEquals(1, Payment::count());
    }

    public function test_cashier_can_reject_a_claim_leaving_violation_unpaid(): void
    {
        $response = $this->actingAs($this->cashier)
            ->post(route('payment-claims.reject', $this->claim), [
                'review_notes' => 'No matching transaction found in GCash account.',
            ]);

        $response->assertRedirect();

        $this->claim->refresh();
        $this->assertEquals('rejected', $this->claim->status);
        $this->assertEquals(0, Payment::count());

        $this->violation->refresh();
        $this->assertEquals('pending', $this->violation->status);
    }

    public function test_guest_can_submit_a_new_claim_after_rejection(): void
    {
        $this->actingAs($this->cashier)->post(route('payment-claims.reject', $this->claim), [
            'review_notes' => 'Amount mismatch.',
        ]);

        $response = $this->post(route('guest-payment.claim', $this->violation->public_payment_token), [
            'claimed_reference' => 'GC-REF-SECOND-ATTEMPT',
            'claimed_amount'    => 1500.00,
        ]);

        $response->assertRedirect(route('guest-payment.claim-status', $this->violation->public_payment_token));
        $this->assertEquals(2, PaymentClaim::count());
    }

    public function test_unauthenticated_user_cannot_access_verification_queue(): void
    {
        $this->get(route('payment-claims.index'))->assertRedirect(route('login'));
        $this->post(route('payment-claims.verify', $this->claim))->assertRedirect(route('login'));
    }

    public function test_non_cashier_role_cannot_verify_claims(): void
    {
        $officer = User::factory()->create(['role' => 'operator']);

        $this->actingAs($officer)
            ->post(route('payment-claims.verify', $this->claim))
            ->assertStatus(403);

        $this->assertEquals(0, Payment::count());
    }
}
