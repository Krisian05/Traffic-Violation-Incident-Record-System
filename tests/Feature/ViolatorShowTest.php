<?php

namespace Tests\Feature;

use App\Models\Lgu;
use App\Models\User;
use App\Models\Violator;
use App\Models\Violation;
use App\Models\ViolationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViolatorShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_violations_by_type_excludes_settled_violations(): void
    {
        $lgu      = Lgu::factory()->create();
        $admin    = User::factory()->create(['role' => 'admin', 'lgu_id' => $lgu->id]);
        $violator = Violator::factory()->create(['lgu_id' => $lgu->id]);

        $typeA = ViolationType::factory()->create(['name' => 'Speeding']);
        $typeB = ViolationType::factory()->create(['name' => 'No Helmet']);

        // 2 active violations (1 pending, 1 overdue)
        Violation::factory()->create([
            'violator_id'       => $violator->id,
            'violation_type_id' => $typeA->id,
            'lgu_id'            => $lgu->id,
            'status'            => 'pending',
        ]);
        Violation::factory()->create([
            'violator_id'       => $violator->id,
            'violation_type_id' => $typeB->id,
            'lgu_id'            => $lgu->id,
            'status'            => 'pending',
        ]);

        // 1 settled violation — should be EXCLUDED from the type breakdown
        Violation::factory()->create([
            'violator_id'       => $violator->id,
            'violation_type_id' => $typeA->id,
            'lgu_id'            => $lgu->id,
            'status'            => 'settled',
        ]);

        $response = $this->actingAs($admin)->get("/violators/{$violator->id}");

        $response->assertStatus(200);

        // $vc (total) = 3, $activeVc = 2, violationsByType should have 2 entries
        $response->assertViewHas('activeVc', 2);

        // violationsByType must only contain non-settled violations
        $response->assertViewHas('violationsByType', function ($byType) {
            // Total count across all active types should be 2
            $total = collect($byType)->sum('count');
            return $total === 2;
        });
    }

    public function test_violations_by_type_hidden_when_all_violations_are_settled(): void
    {
        $lgu      = Lgu::factory()->create();
        $admin    = User::factory()->create(['role' => 'admin', 'lgu_id' => $lgu->id]);
        $violator = Violator::factory()->create(['lgu_id' => $lgu->id]);
        $typeA    = ViolationType::factory()->create(['name' => 'No Seatbelt']);

        // Only settled violations
        Violation::factory()->create([
            'violator_id'       => $violator->id,
            'violation_type_id' => $typeA->id,
            'lgu_id'            => $lgu->id,
            'status'            => 'settled',
        ]);

        $response = $this->actingAs($admin)->get("/violators/{$violator->id}");

        $response->assertStatus(200);
        $response->assertViewHas('activeVc', 0);
        // When all are settled, violationsByType is empty → section hidden
        $response->assertViewHas('violationsByType', function ($byType) {
            return collect($byType)->isEmpty();
        });
        // Section must not be visible in the HTML
        $response->assertDontSee('Active (unsettled) offenses breakdown');
    }
}
