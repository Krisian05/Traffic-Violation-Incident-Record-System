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

class GuestPaymentTest extends TestCase
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
            'code'     => 'CEB',
            'name'     => 'Balamban',
            'province' => 'Cebu',
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

    public function test_guest_can_view_ticket_via_token(): void
    {
        $response = $this->get(route('guest-payment.show', $this->violation->public_payment_token));

        $response->assertStatus(200);
        $response->assertSee('CEB-2026-00001');
        $response->assertSee('Juan');
        $response->assertSee('₱1,500.00');
        $response->assertSee('Pending');
    }

    public function test_garbage_token_returns_404(): void
    {
        $this->get(route('guest-payment.show', 'not-a-real-token'))->assertStatus(404);
    }

    public function test_ticket_number_and_id_are_not_valid_tokens(): void
    {
        $this->get(route('guest-payment.show', $this->violation->ticket_number))->assertStatus(404);
        $this->get(route('guest-payment.show', (string) $this->violation->id))->assertStatus(404);
    }

    public function test_overdue_status_is_computed_and_read_only(): void
    {
        $this->violation->update(['due_date' => now()->subDay()->toDateString()]);

        $response = $this->get(route('guest-payment.show', $this->violation->public_payment_token));

        $response->assertStatus(200);
        $response->assertSee('Overdue');

        $this->violation->refresh();
        $this->assertEquals('pending', $this->violation->status);
    }

    public function test_submitting_a_claim_does_not_settle_the_violation(): void
    {
        $response = $this->post(route('guest-payment.claim', $this->violation->public_payment_token), [
            'claimed_reference' => 'GC-REF-123456',
            'claimed_amount'    => 1500.00,
            'claimant_name'     => 'Juan Dela Cruz',
        ]);

        $response->assertRedirect(route('guest-payment.claim-status', $this->violation->public_payment_token));

        $this->assertEquals(1, PaymentClaim::count());
        $claim = PaymentClaim::first();
        $this->assertEquals('pending_review', $claim->status);
        $this->assertEquals(0, Payment::count());

        $this->violation->refresh();
        $this->assertEquals('pending', $this->violation->status);
    }

    public function test_cannot_submit_a_second_claim_while_one_is_pending(): void
    {
        $this->violation->paymentClaims()->create([
            'payment_method'    => 'gcash',
            'claimed_reference' => 'GC-REF-FIRST',
            'claimed_amount'    => 1500.00,
            'status'            => 'pending_review',
        ]);

        $response = $this->post(route('guest-payment.claim', $this->violation->public_payment_token), [
            'claimed_reference' => 'GC-REF-SECOND',
            'claimed_amount'    => 1500.00,
        ]);

        $response->assertRedirect(route('guest-payment.claim-status', $this->violation->public_payment_token));
        $response->assertSessionHas('error');
        $this->assertEquals(1, PaymentClaim::count());
    }

    public function test_cannot_submit_a_claim_for_an_already_settled_violation(): void
    {
        $this->violation->update(['status' => 'settled']);

        $response = $this->post(route('guest-payment.claim', $this->violation->public_payment_token), [
            'claimed_reference' => 'GC-REF-123456',
            'claimed_amount'    => 1500.00,
        ]);

        $response->assertRedirect(route('guest-payment.show', $this->violation->public_payment_token));
        $this->assertEquals(0, PaymentClaim::count());
    }
}
