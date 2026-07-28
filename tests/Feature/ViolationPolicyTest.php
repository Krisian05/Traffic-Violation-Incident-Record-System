<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Violation;
use App\Models\Violator;
use App\Models\ViolationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class ViolationPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function makeViolation(User $recorder): Violation
    {
        return Violation::create([
            'violator_id'       => Violator::factory()->create()->id,
            'violation_type_id' => ViolationType::factory()->create()->id,
            'recorded_by'       => $recorder->id,
            'date_of_violation' => now()->toDateString(),
            'status'            => 'pending',
        ]);
    }

    // ── Operator tests ────────────────────────────────────────────────────────

    public function test_operator_can_view_any_violation(): void
    {
        $operator  = User::factory()->operator()->create();
        $violation = $this->makeViolation($operator);

        $response = $this->actingAs($operator)->get(route('violations.show', $violation));
        $response->assertOk();
    }

    public function test_operator_can_edit_any_violation(): void
    {
        $operator  = User::factory()->operator()->create();
        $violation = $this->makeViolation($operator);

        $response = $this->actingAs($operator)->get(route('violations.edit', $violation));
        $response->assertOk();
    }

    public function test_operator_can_edit_violation_recorded_by_another_operator(): void
    {
        $operatorA = User::factory()->operator()->create();
        $operatorB = User::factory()->operator()->create();
        $violation = $this->makeViolation($operatorA);

        // Operator B can still edit a violation recorded by Operator A
        $response = $this->actingAs($operatorB)->get(route('violations.edit', $violation));
        $response->assertOk();
    }

    // ── Traffic officer tests ─────────────────────────────────────────────────

    public function test_officer_can_edit_own_violation(): void
    {
        $officer   = User::factory()->trafficOfficer()->create();
        $violation = $this->makeViolation($officer);

        $response = $this->actingAs($officer)->get(route('officer.violations.edit', $violation));
        $response->assertOk();
    }

    public function test_officer_cannot_edit_another_officers_violation(): void
    {
        $officerA  = User::factory()->trafficOfficer()->create();
        $officerB  = User::factory()->trafficOfficer()->create();
        $violation = $this->makeViolation($officerA);

        // Officer B cannot edit a violation recorded by Officer A
        $response = $this->actingAs($officerB)->get(route('officer.violations.edit', $violation));
        $response->assertForbidden();
    }

    public function test_officer_cannot_access_operator_edit_route(): void
    {
        $officer   = User::factory()->trafficOfficer()->create();
        $violation = $this->makeViolation($officer);

        // Officer tries operator portal route — blocked by role middleware
        $response = $this->actingAs($officer)->get(route('violations.edit', $violation));
        $response->assertForbidden();
    }

    // ── Delete / settle ───────────────────────────────────────────────────────

    public function test_only_operator_can_delete_violation(): void
    {
        $operator = User::factory()->operator()->create();
        $officer  = User::factory()->trafficOfficer()->create();
        $violation = $this->makeViolation($operator);

        // Officer cannot delete (role middleware blocks before policy)
        $this->actingAs($officer)
             ->delete(route('violations.destroy', $violation))
             ->assertForbidden();

        // Operator can delete
        $this->actingAs($operator)
             ->delete(route('violations.destroy', $violation))
             ->assertRedirect();

        $this->assertSoftDeleted('violations', ['id' => $violation->id]);
    }

    public function test_violations_index_unified_search_and_strict_status_filters(): void
    {
        $operator = User::factory()->operator()->create();
        $vType    = ViolationType::factory()->create();

        $violatorA = Violator::factory()->create(['first_name' => 'Juan', 'last_name' => 'Dela Cruz']);
        $violatorB = Violator::factory()->create(['first_name' => 'Maria', 'last_name' => 'Clara']);

        $vPending = Violation::create([
            'violator_id'       => $violatorA->id,
            'violation_type_id' => $vType->id,
            'recorded_by'       => $operator->id,
            'date_of_violation' => now()->toDateString(),
            'vehicle_plate'     => 'ABC 1234',
            'status'            => 'pending',
        ]);

        $vPartial = Violation::create([
            'violator_id'       => $violatorB->id,
            'violation_type_id' => $vType->id,
            'recorded_by'       => $operator->id,
            'date_of_violation' => now()->toDateString(),
            'vehicle_plate'     => 'XYZ 9876',
            'status'            => 'partial',
        ]);

        // 1. Test Unified Search by Name
        $resSearchName = $this->actingAs($operator)->get('/violations?search=Juan');
        $resSearchName->assertSee('ABC 1234');
        $resSearchName->assertDontSee('XYZ 9876');

        // 2. Test Unified Search by Plate
        $resSearchPlate = $this->actingAs($operator)->get('/violations?search=XYZ');
        $resSearchPlate->assertSee('XYZ 9876');
        $resSearchPlate->assertDontSee('ABC 1234');

        // 3. Test Strict Pending Filter (MUST NOT include partial)
        $resPending = $this->actingAs($operator)->get('/violations?status=pending');
        $resPending->assertSee('ABC 1234');
        $resPending->assertDontSee('XYZ 9876');

        // 4. Test Partial Filter
        $resPartial = $this->actingAs($operator)->get('/violations?status=partial');
        $resPartial->assertSee('XYZ 9876');
        $resPartial->assertDontSee('ABC 1234');
    }
}
