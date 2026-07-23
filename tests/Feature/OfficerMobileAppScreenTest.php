<?php

namespace Tests\Feature;

use App\Models\Lgu;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Violator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerMobileAppScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_number_follows_standardized_format(): void
    {
        $lgu = Lgu::factory()->create(['code' => 'BAL', 'name' => 'Balamban']);
        $ticketNo = Violation::generateTicketNumber($lgu->id);

        $this->assertMatchesRegularExpression('/^TVIRS-CEB-BAL-\d{4}-\d{6}$/', $ticketNo);
    }

    public function test_officer_can_access_all_12_mobile_application_screens(): void
    {
        $lgu = Lgu::factory()->create(['code' => 'BAL', 'name' => 'Balamban']);
        $officer = User::factory()->create([
            'role'   => 'traffic_officer',
            'lgu_id' => $lgu->id,
        ]);
        $violator = Violator::factory()->create(['lgu_id' => $lgu->id]);
        $violationType = ViolationType::factory()->create();
        $violation = Violation::factory()->create([
            'lgu_id'            => $lgu->id,
            'violator_id'       => $violator->id,
            'violation_type_id' => $violationType->id,
            'recorded_by'       => $officer->id,
        ]);

        // Screen 1: Login Screen (Home welcome view serves as login interface)
        $this->get('/')->assertStatus(200);

        $this->actingAs($officer);

        // Screen 2: Home Dashboard Screen
        $this->get('/officer/dashboard')->assertStatus(200);

        // Screen 3: Issue Citation Screen
        $this->get("/officer/motorists/{$violator->id}/violations/create")->assertStatus(200);

        // Screen 4: Search Violator / Vehicle Screen
        $this->get('/officer/motorists')->assertStatus(200);

        // Screen 5: Select Violation Screen (included in create violation view)
        $this->get("/officer/motorists/{$violator->id}/violations/create")
            ->assertStatus(200)
            ->assertSee('Violation Type');

        // Screen 6 & 7: Capture Evidence & Review Ticket Screen
        $this->get("/officer/violations/{$violation->id}")->assertStatus(200);

        // Screen 8 & 9: Generate QR & Print Thermal Screen
        $this->get("/violations/{$violation->id}/print-thermal")->assertStatus(200);

        // Screen 10: Sync Records Screen
        $this->get('/officer/sync')->assertStatus(200)->assertSee('Synchronization');

        // Screen 11: Incident Report Screen
        $this->get('/officer/incidents/create')->assertStatus(200);

        // Screen 12: Profile and Activity History Screen
        $this->get('/officer/profile')->assertStatus(200)->assertSee($officer->name);
    }
}
