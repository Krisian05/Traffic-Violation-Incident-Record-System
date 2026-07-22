<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidentStatusWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_desktop_store_creates_incident_with_chosen_status_and_records_initial_history(): void
    {
        $operator = User::factory()->operator()->create();

        $response = $this->actingAs($operator)->post(route('incidents.store'), [
            'date_of_incident' => now()->toDateString(),
            'location'         => 'Balamban Highway',
            'status'           => 'under_assessment',
            'motorists'        => [
                ['motorist_name' => 'Juan Dela Cruz'],
            ],
        ]);

        $response->assertRedirect();

        $incident = Incident::firstOrFail();
        $this->assertSame('under_assessment', $incident->status);
        $this->assertSame(1, $incident->statusHistories()->count());
        $history = $incident->statusHistories()->first();
        $this->assertNull($history->from_status);
        $this->assertSame('under_assessment', $history->to_status);
        $this->assertSame($operator->id, $history->changed_by);
    }

    public function test_officer_store_always_starts_incident_as_reported(): void
    {
        $officer = User::factory()->trafficOfficer()->create();

        $response = $this->actingAs($officer)->post(route('officer.incidents.store'), [
            'date_of_incident' => now()->toDateString(),
            'location'         => 'Test Street',
            'motorists'        => [
                ['motorist_name' => 'Juan Dela Cruz'],
            ],
        ]);

        $response->assertRedirect();

        $incident = Incident::firstOrFail();
        $this->assertSame('reported', $incident->status);
        $this->assertSame(1, $incident->statusHistories()->count());
    }

    public function test_operator_can_progress_incident_status_and_history_is_appended(): void
    {
        $operator = User::factory()->operator()->create();
        $this->actingAs($operator)->post(route('incidents.store'), [
            'date_of_incident' => now()->toDateString(),
            'location'         => 'Balamban Highway',
            'status'           => 'reported',
            'motorists'        => [['motorist_name' => 'Juan Dela Cruz']],
        ]);
        $incident = Incident::firstOrFail();

        $response = $this->actingAs($operator)->put(route('incidents.update', $incident), [
            'date_of_incident' => $incident->date_of_incident->toDateString(),
            'location'         => $incident->location,
            'status'           => 'assigned_for_investigation',
            'status_note'      => 'Assigned to Officer Reyes',
            'motorists'        => [['motorist_name' => 'Juan Dela Cruz']],
        ]);

        $response->assertRedirect();

        $incident->refresh();
        $this->assertSame('assigned_for_investigation', $incident->status);
        $this->assertSame(2, $incident->statusHistories()->count());
        $latest = $incident->statusHistories()->orderByDesc('id')->first();
        $this->assertSame('reported', $latest->from_status);
        $this->assertSame('assigned_for_investigation', $latest->to_status);
        $this->assertSame('Assigned to Officer Reyes', $latest->note);
    }

    public function test_officer_can_progress_status_of_own_incident(): void
    {
        $officer = User::factory()->trafficOfficer()->create();
        $this->actingAs($officer)->post(route('officer.incidents.store'), [
            'date_of_incident' => now()->toDateString(),
            'location'         => 'Test Street',
            'motorists'        => [['motorist_name' => 'Juan Dela Cruz']],
        ]);
        $incident = Incident::firstOrFail();
        $this->assertSame('reported', $incident->status);

        $response = $this->actingAs($officer)->put(route('officer.incidents.update', $incident), [
            'date_of_incident' => $incident->date_of_incident->toDateString(),
            'location'         => $incident->location,
            'status'           => 'under_assessment',
        ]);

        $response->assertRedirect();
        $this->assertSame('under_assessment', $incident->fresh()->status);
    }

    public function test_invalid_status_value_is_rejected(): void
    {
        $operator = User::factory()->operator()->create();

        $response = $this->actingAs($operator)->post(route('incidents.store'), [
            'date_of_incident' => now()->toDateString(),
            'location'         => 'Balamban Highway',
            'status'           => 'under_investigation', // old, no-longer-valid value
            'motorists'        => [['motorist_name' => 'Juan Dela Cruz']],
        ]);

        $response->assertSessionHasErrors(['status']);
        $this->assertSame(0, Incident::count());
    }

    public function test_parties_are_recorded_on_store_and_fully_replaced_on_update(): void
    {
        $operator = User::factory()->operator()->create();
        $this->actingAs($operator)->post(route('incidents.store'), [
            'date_of_incident' => now()->toDateString(),
            'location'         => 'Balamban Highway',
            'status'           => 'reported',
            'motorists'        => [['motorist_name' => 'Juan Dela Cruz']],
            'parties'          => [
                ['role' => 'witness', 'name' => 'Pedro Reyes', 'contact_number' => '09171234567'],
            ],
        ]);
        $incident = Incident::firstOrFail();
        $this->assertSame(1, $incident->parties()->count());
        $this->assertSame('witness', $incident->parties()->first()->role);

        $this->actingAs($operator)->put(route('incidents.update', $incident), [
            'date_of_incident' => $incident->date_of_incident->toDateString(),
            'location'         => $incident->location,
            'status'           => $incident->status,
            'motorists'        => [['motorist_name' => 'Juan Dela Cruz']],
            'parties'          => [
                ['role' => 'reporting_party', 'name' => 'Ana Cruz'],
                ['role' => 'other', 'name' => 'Unknown Pedestrian', 'description' => 'Pedestrian, struck by vehicle'],
            ],
        ]);

        $incident->refresh();
        $this->assertSame(2, $incident->parties()->count());
        $this->assertSame(['reporting_party', 'other'], $incident->parties()->pluck('role')->toArray());
    }
}
