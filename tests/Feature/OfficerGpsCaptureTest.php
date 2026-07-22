<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Violator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerGpsCaptureTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_can_record_gps_coordinates_on_a_new_violation(): void
    {
        $officer  = User::factory()->trafficOfficer()->create();
        $violator = Violator::factory()->create();
        $type     = ViolationType::factory()->create();

        $response = $this->actingAs($officer)->post(route('officer.violations.store', $violator), [
            'violation_type_id' => $type->id,
            'date_of_violation' => now()->toDateString(),
            'status'            => 'pending',
            'gps_lat'           => '10.5012345',
            'gps_lng'           => '123.7654321',
        ]);

        $response->assertRedirect();

        $violation = Violation::firstOrFail();
        $this->assertEquals(10.5012345, (float) $violation->gps_lat);
        $this->assertEquals(123.7654321, (float) $violation->gps_lng);
    }

    public function test_violation_gps_coordinates_are_optional(): void
    {
        $officer  = User::factory()->trafficOfficer()->create();
        $violator = Violator::factory()->create();
        $type     = ViolationType::factory()->create();

        $response = $this->actingAs($officer)->post(route('officer.violations.store', $violator), [
            'violation_type_id' => $type->id,
            'date_of_violation' => now()->toDateString(),
            'status'            => 'pending',
        ]);

        $response->assertRedirect();
        $violation = Violation::firstOrFail();
        $this->assertNull($violation->gps_lat);
        $this->assertNull($violation->gps_lng);
    }

    public function test_violation_gps_latitude_out_of_range_is_rejected(): void
    {
        $officer  = User::factory()->trafficOfficer()->create();
        $violator = Violator::factory()->create();
        $type     = ViolationType::factory()->create();

        $response = $this->actingAs($officer)->post(route('officer.violations.store', $violator), [
            'violation_type_id' => $type->id,
            'date_of_violation' => now()->toDateString(),
            'status'            => 'pending',
            'gps_lat'           => '190',
            'gps_lng'           => '10',
        ]);

        $response->assertSessionHasErrors('gps_lat');
        $this->assertSame(0, Violation::count());
    }

    public function test_officer_can_record_gps_coordinates_on_a_new_incident(): void
    {
        $officer = User::factory()->trafficOfficer()->create();

        $response = $this->actingAs($officer)->post(route('officer.incidents.store'), [
            'date_of_incident' => now()->toDateString(),
            'location'         => 'Test Street',
            'gps_lat'          => '10.3333333',
            'gps_lng'          => '123.9999999',
            'motorists'        => [
                ['motorist_name' => 'Juan Dela Cruz'],
            ],
        ]);

        $response->assertRedirect();

        $incident = Incident::firstOrFail();
        $this->assertEquals(10.3333333, (float) $incident->gps_lat);
        $this->assertEquals(123.9999999, (float) $incident->gps_lng);
    }
}
