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
}
