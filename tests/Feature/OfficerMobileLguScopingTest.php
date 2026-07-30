<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Lgu;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Violator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerMobileLguScopingTest extends TestCase
{
    use RefreshDatabase;

    private User $officerLgu1;
    private User $officerLgu2;
    private Lgu $lgu1;
    private Lgu $lgu2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lgu1 = Lgu::factory()->create(['name' => 'LGU One']);
        $this->lgu2 = Lgu::factory()->create(['name' => 'LGU Two']);

        $this->officerLgu1 = User::factory()->issuingOfficer()->create(['lgu_id' => $this->lgu1->id]);
        $this->officerLgu2 = User::factory()->issuingOfficer()->create(['lgu_id' => $this->lgu2->id]);
    }

    public function test_officer_dashboard_stats_are_scoped_strictly_by_lgu(): void
    {
        $vType = ViolationType::factory()->create();
        $violator = Violator::factory()->create();

        // Violation & Incident in LGU 1
        Violation::factory()->create([
            'lgu_id' => $this->lgu1->id,
            'violator_id' => $violator->id,
            'violation_type_id' => $vType->id,
            'recorded_by' => $this->officerLgu1->id,
        ]);

        Incident::factory()->create([
            'lgu_id' => $this->lgu1->id,
            'recorded_by' => $this->officerLgu1->id,
        ]);

        // Violation & Incident in LGU 2
        Violation::factory()->create([
            'lgu_id' => $this->lgu2->id,
            'violator_id' => $violator->id,
            'violation_type_id' => $vType->id,
            'recorded_by' => $this->officerLgu2->id,
        ]);

        Incident::factory()->create([
            'lgu_id' => $this->lgu2->id,
            'recorded_by' => $this->officerLgu2->id,
        ]);

        $response1 = $this->actingAs($this->officerLgu1)->get('/officer/dashboard');
        $response1->assertOk();
        $response1->assertViewHas('violationCount', 1);
        $response1->assertViewHas('incidentCount', 1);

        $response2 = $this->actingAs($this->officerLgu2)->get('/officer/dashboard');
        $response2->assertOk();
        $response2->assertViewHas('violationCount', 1);
        $response2->assertViewHas('incidentCount', 1);
    }

    public function test_officer_incidents_index_is_scoped_by_lgu(): void
    {
        $incLgu1 = Incident::factory()->create([
            'lgu_id' => $this->lgu1->id,
            'location' => 'LGU 1 Location Alpha',
            'recorded_by' => $this->officerLgu1->id,
        ]);

        $incLgu2 = Incident::factory()->create([
            'lgu_id' => $this->lgu2->id,
            'location' => 'LGU 2 Location Beta',
            'recorded_by' => $this->officerLgu2->id,
        ]);

        $res1 = $this->actingAs($this->officerLgu1)->get('/officer/incidents');
        $res1->assertOk();
        $res1->assertSee('LGU 1 Location Alpha');
        $res1->assertDontSee('LGU 2 Location Beta');
    }

    public function test_officer_cannot_view_cross_lgu_violation(): void
    {
        $vType = ViolationType::factory()->create();
        $violator = Violator::factory()->create();

        $violationLgu2 = Violation::factory()->create([
            'lgu_id' => $this->lgu2->id,
            'violator_id' => $violator->id,
            'violation_type_id' => $vType->id,
            'recorded_by' => $this->officerLgu2->id,
        ]);

        $this->actingAs($this->officerLgu1)
            ->get('/officer/violations/' . $violationLgu2->id)
            ->assertStatus(403);
    }

    public function test_officer_cannot_view_cross_lgu_incident(): void
    {
        $incLgu2 = Incident::factory()->create([
            'lgu_id' => $this->lgu2->id,
            'recorded_by' => $this->officerLgu2->id,
        ]);

        $this->actingAs($this->officerLgu1)
            ->get('/officer/incidents/' . $incLgu2->id)
            ->assertStatus(403);
    }
}
