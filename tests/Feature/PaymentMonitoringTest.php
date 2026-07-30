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

class PaymentMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private function makeViolation(array $overrides = []): Violation
    {
        // Explicit unique name: violation_types.name has a DB unique constraint,
        // and ViolationTypeFactory's fake()->unique()->randomElement() pool is
        // small enough to overflow across a full suite run — sidestep both.
        $type = $overrides['violationType'] ?? ViolationType::factory()->create([
            'name'                => 'Test Type ' . uniqid(),
            'fine_amount'         => 1000,
            'late_penalty_amount' => 200,
        ]);
        unset($overrides['violationType']);

        return Violation::create(array_merge([
            'violator_id'       => Violator::factory()->create()->id,
            'violation_type_id' => $type->id,
            'recorded_by'       => User::factory()->operator()->create()->id,
            'date_of_violation' => now()->toDateString(),
            'status'            => 'pending',
        ], $overrides));
    }

    // ── Due date / late penalty automation ──────────────────────────────────

    public function test_due_date_is_auto_set_from_grace_period(): void
    {
        $violation = $this->makeViolation(['date_of_violation' => now()->toDateString()]);

        $graceDays = (int) config('tvirs.payment.grace_period_days', 3);
        $this->assertNotNull($violation->due_date);
        $this->assertSame(
            now()->addDays($graceDays)->toDateString(),
            $violation->due_date->toDateString()
        );
    }

    public function test_violation_is_overdue_only_after_due_date_and_carries_late_penalty(): void
    {
        $type = ViolationType::factory()->create(['fine_amount' => 1000, 'late_penalty_amount' => 200]);

        $notYetDue = $this->makeViolation(['violationType' => $type, 'due_date' => now()->addDay()->toDateString()]);
        $this->assertFalse($notYetDue->isOverdue());
        $this->assertSame(0.0, $notYetDue->latePenaltyAmount());
        $this->assertEqualsWithDelta(1000.0, $notYetDue->totalAmountDue(), 0.001);

        $pastDue = $this->makeViolation(['violationType' => $type, 'due_date' => now()->subDay()->toDateString()]);
        $this->assertTrue($pastDue->isOverdue());
        $this->assertEqualsWithDelta(200.0, $pastDue->latePenaltyAmount(), 0.001);
        $this->assertEqualsWithDelta(1200.0, $pastDue->totalAmountDue(), 0.001);
    }

    // ── Settlement: single write path via PaymentService ────────────────────

    public function test_full_settlement_creates_payment_row_and_marks_violation_settled(): void
    {
        $lgu      = Lgu::factory()->create();
        $cashier  = User::factory()->cashier()->create(['lgu_id' => $lgu->id]);
        $violation = $this->makeViolation(['lgu_id' => $lgu->id]);

        $response = $this->actingAs($cashier)->patch(route('violations.settle', $violation), [
            'or_number'      => 'OR-0001',
            'cashier_name'   => 'Juan Cruz',
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();
        $violation->refresh();
        $this->assertSame('settled', $violation->status);
        $this->assertNotNull($violation->settled_at);
        $this->assertSame(1, $violation->payments()->count());
        $this->assertEqualsWithDelta(1000.0, (float) $violation->payments()->sum('amount_paid'), 0.001);
        $this->assertEqualsWithDelta(0.0, $violation->balanceRemaining(), 0.001);
    }

    public function test_settlement_from_cashier_redirects_to_exact_ticket_number_page(): void
    {
        $lgu       = Lgu::factory()->create();
        $cashier   = User::factory()->cashier()->create(['lgu_id' => $lgu->id]);
        $violation = $this->makeViolation(['lgu_id' => $lgu->id, 'ticket_number' => 'TVIRS-TEST-0001']);

        $response = $this->actingAs($cashier)
            ->from(route('violations.cashier', ['search' => 'Juan Motorist']))
            ->patch(route('violations.settle', $violation), [
                'or_number'      => 'OR-9999',
                'cashier_name'   => 'Juan Cruz',
                'payment_method' => 'cash',
            ]);

        $response->assertRedirect(route('violations.cashier', ['search' => 'TVIRS-TEST-0001']));
        $violation->refresh();
        $this->assertSame('settled', $violation->status);
    }

    public function test_partial_payment_sets_status_partial_then_settled_on_second_payment(): void
    {
        $lgu       = Lgu::factory()->create();
        $cashier   = User::factory()->cashier()->create(['lgu_id' => $lgu->id]);
        $violation = $this->makeViolation(['lgu_id' => $lgu->id]);

        $this->actingAs($cashier)->patch(route('violations.settle', $violation), [
            'or_number'      => 'OR-1001',
            'cashier_name'   => 'Juan Cruz',
            'payment_method' => 'cash',
            'amount_paid'    => 400,
        ])->assertRedirect();

        $violation->refresh();
        $this->assertSame('partial', $violation->status);
        $this->assertNull($violation->settled_at);
        $this->assertEqualsWithDelta(600.0, $violation->balanceRemaining(), 0.001);

        $this->actingAs($cashier)->patch(route('violations.settle', $violation), [
            'or_number'      => 'OR-1002',
            'cashier_name'   => 'Juan Cruz',
            'payment_method' => 'gcash',
        ])->assertRedirect();

        $violation->refresh();
        $this->assertSame('settled', $violation->status);
        $this->assertNotNull($violation->settled_at);
        $this->assertSame(2, $violation->payments()->count());
        $this->assertEqualsWithDelta(0.0, $violation->balanceRemaining(), 0.001);
    }

    public function test_amount_paid_exceeding_balance_is_rejected(): void
    {
        $lgu       = Lgu::factory()->create();
        $cashier   = User::factory()->cashier()->create(['lgu_id' => $lgu->id]);
        $violation = $this->makeViolation(['lgu_id' => $lgu->id]);

        $response = $this->actingAs($cashier)->patch(route('violations.settle', $violation), [
            'or_number'      => 'OR-2001',
            'cashier_name'   => 'Juan Cruz',
            'payment_method' => 'cash',
            'amount_paid'    => 5000,
        ]);

        $response->assertSessionHasErrors(['amount_paid']);
        $this->assertSame('pending', $violation->fresh()->status);
        $this->assertSame(0, Payment::count());
    }

    public function test_or_number_must_be_unique_across_all_payments(): void
    {
        $lgu     = Lgu::factory()->create();
        $cashier = User::factory()->cashier()->create(['lgu_id' => $lgu->id]);

        $violationA = $this->makeViolation(['lgu_id' => $lgu->id]);
        $violationB = $this->makeViolation(['lgu_id' => $lgu->id]);

        $this->actingAs($cashier)->patch(route('violations.settle', $violationA), [
            'or_number'      => 'OR-DUPLICATE',
            'cashier_name'   => 'Juan Cruz',
            'payment_method' => 'cash',
        ])->assertRedirect();

        $response = $this->actingAs($cashier)->patch(route('violations.settle', $violationB), [
            'or_number'      => 'OR-DUPLICATE',
            'cashier_name'   => 'Juan Cruz',
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors(['or_number']);
        $this->assertSame('pending', $violationB->fresh()->status);
    }

    // ── Authorization: settlement restricted to Cashier and Treasurer ─────────

    public function test_operator_cannot_settle_a_violation_directly(): void
    {
        $operator  = User::factory()->operator()->create();
        $violation = $this->makeViolation();

        $response = $this->actingAs($operator)->patch(route('violations.settle', $violation), [
            'or_number'      => 'OR-3001',
            'cashier_name'   => 'Front Desk',
            'payment_method' => 'cash',
        ]);

        $response->assertForbidden();
        $this->assertSame('pending', $violation->fresh()->status);
        $this->assertSame(0, Payment::where('violation_id', $violation->id)->count());
    }

    public function test_treasurer_can_settle_a_violation_in_their_lgu(): void
    {
        $lgu       = Lgu::factory()->create();
        $treasurer = User::factory()->treasurer()->create(['lgu_id' => $lgu->id]);
        $violation = $this->makeViolation(['lgu_id' => $lgu->id]);

        $response = $this->actingAs($treasurer)->patch(route('violations.settle', $violation), [
            'or_number'      => 'OR-TREAS-01',
            'cashier_name'   => 'Treasurer Office',
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertSame('settled', $violation->fresh()->status);
    }

    public function test_cashier_cannot_settle_violation_outside_their_lgu(): void
    {
        $lguA      = Lgu::factory()->create();
        $lguB      = Lgu::factory()->create();
        $cashier   = User::factory()->cashier()->create(['lgu_id' => $lguA->id]);
        $violation = $this->makeViolation(['lgu_id' => $lguB->id]);

        $response = $this->actingAs($cashier)->patch(route('violations.settle', $violation), [
            'or_number'      => 'OR-4001',
            'cashier_name'   => 'Juan Cruz',
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(403);
        $this->assertSame(0, Payment::count());
    }

    public function test_edit_form_cannot_change_status_away_from_settled(): void
    {
        $lgu       = Lgu::factory()->create();
        $cashier   = User::factory()->cashier()->create(['lgu_id' => $lgu->id]);
        $operator  = User::factory()->operator()->create(['lgu_id' => $lgu->id]);
        $violation = $this->makeViolation(['lgu_id' => $lgu->id]);

        $this->actingAs($cashier)->patch(route('violations.settle', $violation), [
            'or_number'      => 'OR-5001',
            'cashier_name'   => 'Front Desk',
            'payment_method' => 'cash',
        ])->assertRedirect();

        $violation->refresh();
        $this->assertSame('settled', $violation->status);
        $paymentCountBefore = Payment::count();

        // Regression guard: the operator edit form used to be able to flip
        // status back to "pending" directly, bypassing the payments table.
        $response = $this->actingAs($operator)->put(route('violations.update', $violation), [
            'violation_type_id' => $violation->violation_type_id,
            'date_of_violation' => $violation->date_of_violation->toDateString(),
            'status'            => 'pending',
            'notes'             => 'attempted tamper',
        ]);

        $response->assertRedirect();
        $violation->refresh();
        $this->assertSame('settled', $violation->status, 'Settled status must not be changeable from the edit form.');
        $this->assertSame($paymentCountBefore, Payment::count());
    }

    // ── Collection Reporting & Treasurer access ──────────────────────────────

    public function test_treasurer_is_scoped_to_their_own_lgu_on_collection_report(): void
    {
        $lguA      = Lgu::factory()->create();
        $lguB      = Lgu::factory()->create();
        $cashierA  = User::factory()->cashier()->create(['lgu_id' => $lguA->id]);
        $cashierB  = User::factory()->cashier()->create(['lgu_id' => $lguB->id]);
        $treasurer = User::factory()->treasurer()->create(['lgu_id' => $lguA->id]);

        $violationA = $this->makeViolation(['lgu_id' => $lguA->id]);
        $violationB = $this->makeViolation(['lgu_id' => $lguB->id]);

        $this->actingAs($cashierA)->patch(route('violations.settle', $violationA), [
            'or_number' => 'OR-LGU-A', 'cashier_name' => 'A', 'payment_method' => 'cash',
        ])->assertRedirect();

        $this->actingAs($cashierB)->patch(route('violations.settle', $violationB), [
            'or_number' => 'OR-LGU-B', 'cashier_name' => 'B', 'payment_method' => 'cash',
        ])->assertRedirect();

        // Treasurer requests the report and even tries to tamper the lgu_id filter — still only sees their own LGU.
        $response = $this->actingAs($treasurer)->get(route('payments.report', ['lgu_id' => $lguB->id]));

        $response->assertOk();
        $response->assertSee('OR-LGU-A');
        $response->assertDontSee('OR-LGU-B');
    }

    public function test_cashier_can_access_collection_report_and_traffic_officer_cannot(): void
    {
        $cashier = User::factory()->cashier()->create();
        $officer = User::factory()->trafficOfficer()->create();

        $this->actingAs($cashier)->get(route('payments.report'))->assertOk();
        $this->actingAs($officer)->get(route('payments.report'))->assertForbidden();
    }

    public function test_admin_and_province_admin_can_access_collection_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $provinceAdmin = User::factory()->provinceAdmin()->create();

        $this->actingAs($admin)->get(route('payments.report'))->assertOk();
        $this->actingAs($provinceAdmin)->get(route('payments.report'))->assertOk();
    }
}
