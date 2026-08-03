<?php

namespace Tests\Feature;

use App\Models\Lgu;
use App\Models\Payment;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Violator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlinePaymentTest extends TestCase
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

        $this->admin = User::factory()->create([
            'role'     => 'admin',
            'username' => 'admin',
        ]);

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

    /** @test */
    public function public_user_can_access_online_payment_search_page()
    {
        $response = $this->get(route('online-payment.index'));

        $response->assertStatus(200);
        $response->assertSee('Traffic Citation Online Payment');
    }

    /** @test */
    public function user_can_search_citation_by_ticket_number()
    {
        $response = $this->get(route('online-payment.index', ['search' => 'CEB-2026-00001']));

        $response->assertStatus(200);
        $response->assertSee('CEB-2026-00001');
        $response->assertSee('Juan');
        $response->assertSee('₱1,500.00');
    }

    /** @test */
    public function scanning_ticket_qr_code_opens_online_checkout()
    {
        $response = $this->get(route('online-payment.checkout', 'CEB-2026-00001'));

        $response->assertStatus(200);
        $response->assertSee('CEB-2026-00001');
        $response->assertSee('No Helmet');
        $response->assertSee('GCash');
        $response->assertSee('Maya');
        $response->assertSee('₱1,500.00');
    }

    /** @test */
    public function processing_online_gcash_payment_redirects_to_verification_gate_and_confirming_received_marks_settled()
    {
        $this->assertEquals('pending', $this->violation->status);
        $this->assertEquals(0, Payment::count());

        $processResponse = $this->post(route('online-payment.process', $this->violation), [
            'payment_method' => 'gcash',
            'mobile_number'  => '09171234567',
        ]);

        // Assert process redirects to gcash gateway portal
        $processResponse->assertStatus(302);
        $this->assertStringContainsString('/gcash', $processResponse->headers->get('Location'));

        // Confirm money received
        $response = $this->post(route('online-payment.confirm-received', [
            'violation' => $this->violation->id,
            'ref'       => 'TXN-TEST-12345',
        ]), [
            'payment_method' => 'gcash',
        ]);

        // Assert payment recorded in database
        $this->assertEquals(1, Payment::count());
        $payment = Payment::first();

        $this->assertEquals($this->violation->id, $payment->violation_id);
        $this->assertEquals('gcash', $payment->payment_method);
        $this->assertEquals(1500.00, $payment->amount_paid);
        $this->assertStringStartsWith('OR-ONL-', $payment->or_number);
        $this->assertStringContainsString('Online Merchant Gateway', $payment->cashier_name);

        // Assert violation is now settled
        $this->violation->refresh();
        $this->assertEquals('settled', $this->violation->status);

        // Assert redirection to digital receipt
        $response->assertRedirect(route('online-payment.receipt', $payment));
    }

    /** @test */
    public function user_can_view_digital_receipt()
    {
        $payment = Payment::create([
            'violation_id'   => $this->violation->id,
            'amount_paid'    => 1500.00,
            'payment_method' => 'gcash',
            'or_number'      => 'OR-ONL-20260803-TEST1',
            'cashier_name'   => 'Online Portal (GCash Express)',
            'paid_at'        => now(),
        ]);

        $response = $this->get(route('online-payment.receipt', $payment));

        $response->assertStatus(200);
        $response->assertSee('OR-ONL-20260803-TEST1');
        $response->assertSee('Juan');
        $response->assertSee('₱1,500.00');
    }

    /** @test */
    public function attempting_to_pay_already_settled_ticket_is_blocked()
    {
        $this->violation->update(['status' => 'settled']);

        $response = $this->post(route('online-payment.process', $this->violation), [
            'payment_method' => 'gcash',
        ]);

        $response->assertRedirect(route('online-payment.checkout', $this->violation->ticket_number));
        $response->assertSessionHas('error');
    }
}
