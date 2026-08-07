<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProvinceDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_province_admin_can_access_province_dashboard(): void
    {
        $provinceAdmin = User::factory()->create([
            'role' => 'province_admin',
        ]);

        $response = $this->actingAs($provinceAdmin)->get(route('province.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Provincial Command Dashboard');
        $response->assertSee('Comparative LGU Performance Matrix');
    }

    public function test_province_admin_can_filter_province_dashboard_by_lgu_and_year(): void
    {
        $lgu = \App\Models\Lgu::factory()->create(['name' => 'Balamban']);
        $provinceAdmin = User::factory()->create([
            'role' => 'province_admin',
        ]);

        $response = $this->actingAs($provinceAdmin)->get(route('province.dashboard', [
            'lgu_id' => $lgu->id,
            'year'   => 2026,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Balamban');
    }
}
