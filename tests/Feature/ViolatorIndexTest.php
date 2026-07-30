<?php

namespace Tests\Feature;

use App\Models\Lgu;
use App\Models\User;
use App\Models\Violator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViolatorIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_violators_index_page_loads_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'lgu_id' => null]);
        $response = $this->actingAs($admin)->get('/violators');
        $response->assertStatus(200);
    }

    public function test_violators_index_page_loads_for_cashier_with_lgu(): void
    {
        $lgu = Lgu::factory()->create();
        $cashier = User::factory()->create(['role' => 'cashier', 'lgu_id' => $lgu->id]);
        $violator = Violator::factory()->create(['lgu_id' => $lgu->id]);

        $response = $this->actingAs($cashier)->get('/violators');
        $response->assertStatus(200);
    }

    public function test_violators_index_page_loads_with_lgu_sort(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get('/violators?sort=lgu');
        $response->assertStatus(200);
    }
}
