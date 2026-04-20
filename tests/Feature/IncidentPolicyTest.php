<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidentPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function makeIncident(User $recorder): Incident
    {
        return Incident::create([
            'recorded_by'      => $recorder->id,
            'date_of_incident' => now()->toDateString(),
            'location'         => 'Test Location',
            'status'           => 'under_investigation',
        ]);
    }

    // ── Operator tests ────────────────────────────────────────────────────────

    public function test_operator_can_edit_any_incident(): void
    {
        $operator = User::factory()->operator()->create();
        $incident = $this->makeIncident($operator);

        $response = $this->actingAs($operator)->get(route('incidents.edit', $incident));
        $response->assertOk();
    }

    public function test_operator_can_edit_incident_recorded_by_another_operator(): void
    {
        $operatorA = User::factory()->operator()->create();
        $operatorB = User::factory()->operator()->create();
        $incident  = $this->makeIncident($operatorA);

        $response = $this->actingAs($operatorB)->get(route('incidents.edit', $incident));
        $response->assertOk();
    }

    public function test_operator_can_delete_incident(): void
    {
        $operator = User::factory()->operator()->create();
        $incident = $this->makeIncident($operator);

        $this->actingAs($operator)
             ->delete(route('incidents.destroy', $incident))
             ->assertRedirect(route('incidents.index'));

        $this->assertSoftDeleted('incidents', ['id' => $incident->id]);
    }

    // ── Traffic officer tests ─────────────────────────────────────────────────

    public function test_officer_can_edit_own_incident(): void
    {
        $officer  = User::factory()->trafficOfficer()->create();
        $incident = $this->makeIncident($officer);

        $response = $this->actingAs($officer)->get(route('officer.incidents.edit', $incident));
        $response->assertOk();
    }

    public function test_officer_cannot_edit_another_officers_incident(): void
    {
        $officerA = User::factory()->trafficOfficer()->create();
        $officerB = User::factory()->trafficOfficer()->create();
        $incident = $this->makeIncident($officerA);

        $response = $this->actingAs($officerB)->get(route('officer.incidents.edit', $incident));
        $response->assertForbidden();
    }

    public function test_officer_cannot_access_operator_delete_route(): void
    {
        $officer  = User::factory()->trafficOfficer()->create();
        $incident = $this->makeIncident($officer);

        // Role middleware blocks before policy even runs
        $this->actingAs($officer)
             ->delete(route('incidents.destroy', $incident))
             ->assertForbidden();
    }
}
