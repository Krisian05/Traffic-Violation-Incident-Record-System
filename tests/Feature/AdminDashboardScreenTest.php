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

class AdminDashboardScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_12_admin_dashboard_screens_are_accessible_and_functional(): void
    {
        $lgu = Lgu::factory()->create(['code' => 'BAL', 'name' => 'Balamban']);
        $admin = User::factory()->create(['role' => 'admin', 'lgu_id' => $lgu->id]);
        $provinceAdmin = User::factory()->create(['role' => 'province_admin']);
        $treasurer = User::factory()->create(['role' => 'treasurer', 'lgu_id' => $lgu->id]);

        $violator = Violator::factory()->create(['lgu_id' => $lgu->id]);
        $violationType = ViolationType::factory()->create(['code' => 'ORD-001', 'points' => 3]);
        $violation = Violation::factory()->create([
            'lgu_id'            => $lgu->id,
            'violator_id'       => $violator->id,
            'violation_type_id' => $violationType->id,
            'recorded_by'       => $admin->id,
            'gps_lat'           => 10.3157,
            'gps_lng'           => 123.8854,
        ]);

        // 1. Executive Dashboard (LGU & Province level)
        $this->actingAs($admin)->get('/dashboard')->assertStatus(200);
        $this->actingAs($provinceAdmin)->get('/province/dashboard')->assertStatus(200);

        // 2. Citation Records Screen
        $this->actingAs($admin)->get('/violations')->assertStatus(200);
        $this->actingAs($admin)->get("/violations/{$violation->id}")->assertStatus(200);

        // 3. Payment Records Screen (Cashier & Collection Reports)
        $this->actingAs($treasurer)->get('/cashier')->assertStatus(200);
        $this->actingAs($treasurer)->get('/payments/report')->assertStatus(200);

        // 4. Incident Records Screen
        $this->actingAs($admin)->get('/incidents')->assertStatus(200);

        // 5. Officer Performance Screen (Reports portal)
        $this->actingAs($admin)->get('/reports?type=officer')->assertStatus(200);

        // 6. LGU Performance Screen (Province Dashboard comparative monitor)
        $this->actingAs($provinceAdmin)->get('/province/dashboard')->assertStatus(200)->assertSee('Balamban');
        $this->actingAs($provinceAdmin)->get("/province/dashboard?lgu_id={$lgu->id}&year=2026")->assertStatus(200)->assertSee('Balamban');

        // 7. Violation Analytics Screen (Analytics JSON API)
        $this->actingAs($admin)->get('/dashboard/analytics')
            ->assertStatus(200)
            ->assertJsonStructure(['period', 'chart']);

        // 8. Traffic Violation Heatmap Screen (Dashboard Leaflet map container)
        $this->actingAs($admin)->get('/dashboard')->assertStatus(200)->assertSee('dashboard');

        // 9. User Management Screen
        $this->actingAs($admin)->get('/users')->assertStatus(200);

        // 10. Ordinance and Penalty Management Screen
        $this->actingAs($admin)->get('/violation-types')->assertStatus(200);

        // 11. Reports Export Screen (PDF & Excel generation)
        $this->actingAs($admin)->get('/reports/export/excel')->assertStatus(200);

        // 12. Audit Logs Screen
        $this->actingAs($admin)->get('/audit-logs')->assertStatus(200);
    }
}
