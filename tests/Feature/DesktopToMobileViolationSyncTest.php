<?php

namespace Tests\Feature;

use App\Models\Lgu;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Violator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesktopToMobileViolationSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_desktop_created_violation_is_visible_to_mobile_officer(): void
    {
        $lgu = Lgu::create(['name' => 'Balamban', 'code' => 'BAL']);

        $operator = User::factory()->create([
            'role' => 'operator',
            'lgu_id' => $lgu->id,
        ]);

        $officer = User::factory()->create([
            'role' => 'traffic_officer',
            'lgu_id' => $lgu->id,
        ]);

        $violator = Violator::create([
            'first_name' => 'Ervin',
            'last_name' => 'Dela Cruz',
            'lgu_id' => $lgu->id,
        ]);

        $vType = ViolationType::create([
            'name' => 'No Helmet',
            'fine_amount' => 500.00,
        ]);

        // Operator creates violation on Desktop
        $this->actingAs($operator)->post(route('violations.store', $violator), [
            'violation_type_id' => $vType->id,
            'date_of_violation' => now()->toDateString(),
            'status' => 'pending',
            'location' => 'Balamban Public Market',
        ]);

        $this->assertDatabaseHas('violations', [
            'violator_id' => $violator->id,
            'violation_type_id' => $vType->id,
        ]);

        // Traffic Officer opens Ervin's motorist profile on Mobile
        $response = $this->actingAs($officer)->get(route('officer.motorists.show', $violator));

        $response->assertStatus(200);
        $response->assertSee('No Helmet');
        $response->assertSee('Balamban Public Market');
    }
}
