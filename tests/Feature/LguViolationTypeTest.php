<?php

namespace Tests\Feature;

use App\Models\Lgu;
use App\Models\User;
use App\Models\ViolationType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LguViolationTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_province_admin_sees_all_violation_types_across_lgus_and_has_read_only_access(): void
    {
        $lgu1 = Lgu::factory()->create(['name' => 'Balamban']);
        $lgu2 = Lgu::factory()->create(['name' => 'Asturias']);

        $vTypeLgu1 = ViolationType::factory()->create(['name' => 'Balamban Ordinance 101', 'lgu_id' => $lgu1->id]);
        $vTypeLgu2 = ViolationType::factory()->create(['name' => 'Asturias Ordinance 202', 'lgu_id' => $lgu2->id]);

        $provAdmin = User::factory()->create(['role' => 'province_admin']);

        $res = $this->actingAs($provAdmin)->get('/violation-types');
        $res->assertOk();
        $res->assertSee('Balamban Ordinance 101');
        $res->assertSee('Asturias Ordinance 202');
        $res->assertDontSee('+ Add Type');

        // Cannot access create or store
        $this->actingAs($provAdmin)->get('/violation-types/create')->assertStatus(403);
        $this->actingAs($provAdmin)->post('/violation-types', [
            'lgu_id' => $lgu1->id,
            'name'   => 'Unauthorized Violation',
        ])->assertStatus(403);
    }

    public function test_lgu_admin_can_create_and_manage_own_lgu_violation_type(): void
    {
        $lgu = Lgu::factory()->create(['name' => 'Barili']);
        $lguAdmin = User::factory()->lguAdmin()->create(['lgu_id' => $lgu->id]);

        $res = $this->actingAs($lguAdmin)->post('/violation-types', [
            'name'        => 'Barili Illegal Parking',
            'code'        => 'BAR-PARK',
            'fine_amount' => 750.00,
        ]);
        $res->assertRedirect(route('violation-types.index'));

        $this->assertDatabaseHas('violation_types', [
            'name'        => 'Barili Illegal Parking',
            'lgu_id'      => $lgu->id,
            'fine_amount' => 750.00,
        ]);
    }

    public function test_lgu_admin_cannot_edit_another_lgus_violation_type(): void
    {
        $lgu1 = Lgu::factory()->create();
        $lgu2 = Lgu::factory()->create();

        $lgu1Admin = User::factory()->lguAdmin()->create(['lgu_id' => $lgu1->id]);
        $lgu2Type  = ViolationType::factory()->create(['lgu_id' => $lgu2->id, 'name' => 'LGU 2 Special Violation']);

        $this->actingAs($lgu1Admin)
            ->get(route('violation-types.edit', $lgu2Type))
            ->assertStatus(403);

        $this->actingAs($lgu1Admin)
            ->put(route('violation-types.update', $lgu2Type), [
                'name' => 'Tampered Name',
            ])
            ->assertStatus(403);
    }
}
