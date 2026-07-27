<?php

namespace Tests\Feature;

use App\Models\Lgu;
use App\Models\User;
use App\Models\Violation;
use App\Models\Incident;
use App\Models\Violator;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacFrameworkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Lgu::factory()->create(['id' => 1, 'name' => 'Cebu City', 'code' => 'CEB']);
    }

    /** 1. Super Administrator Tests */
    public function test_super_admin_has_full_access(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/province/dashboard')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/users')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/lgus')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/audit-logs')
            ->assertOk();
    }

    /** 2. Provincial Administrator Tests */
    public function test_provincial_admin_access(): void
    {
        $provinceAdmin = User::factory()->provinceAdmin()->create();

        $this->actingAs($provinceAdmin)
            ->get('/province/dashboard')
            ->assertOk();

        $this->actingAs($provinceAdmin)
            ->get('/reports')
            ->assertOk();

        $this->actingAs($provinceAdmin)
            ->get('/payments/report')
            ->assertOk();

        // Restricted from system administration (LGUs)
        $this->actingAs($provinceAdmin)
            ->get('/lgus')
            ->assertStatus(403);
    }

    /** 3. LGU Administrator Tests */
    public function test_lgu_admin_access(): void
    {
        $lguAdmin = User::factory()->lguAdmin()->create(['lgu_id' => 1]);

        $this->actingAs($lguAdmin)
            ->get('/dashboard')
            ->assertOk();

        $this->actingAs($lguAdmin)
            ->get('/violators')
            ->assertOk();

        $this->actingAs($lguAdmin)
            ->get('/violations')
            ->assertOk();

        $this->actingAs($lguAdmin)
            ->get('/users')
            ->assertOk();

        // Cannot manage LGUs
        $this->actingAs($lguAdmin)
            ->get('/lgus')
            ->assertStatus(403);
    }

    /** 4. Treasurer & Cashier Tests */
    public function test_treasurer_and_cashier_access(): void
    {
        $treasurer = User::factory()->treasurer()->create(['lgu_id' => 1]);
        $cashier = User::factory()->cashier()->create(['lgu_id' => 1]);

        $this->actingAs($treasurer)
            ->get('/cashier')
            ->assertOk();

        $this->actingAs($cashier)
            ->get('/cashier')
            ->assertOk();

        // Restricted from user management
        $this->actingAs($treasurer)
            ->get('/users')
            ->assertStatus(403);

        $this->actingAs($cashier)
            ->get('/users')
            ->assertStatus(403);
    }

    /** 5. Police / Traffic Supervisor Tests */
    public function test_traffic_supervisor_access(): void
    {
        $supervisor = User::factory()->trafficSupervisor()->create(['lgu_id' => 1]);

        $this->actingAs($supervisor)
            ->get('/violations')
            ->assertOk();

        $this->actingAs($supervisor)
            ->get('/incidents')
            ->assertOk();

        $this->actingAs($supervisor)
            ->get('/reports')
            ->assertOk();

        // Cannot delete violations or manage users
        $this->actingAs($supervisor)
            ->get('/users')
            ->assertStatus(403);
    }

    /** 6. Issuing Officer Tests */
    public function test_issuing_officer_mobile_access(): void
    {
        $officer = User::factory()->issuingOfficer()->create(['lgu_id' => 1]);

        $this->actingAs($officer)
            ->get('/officer/dashboard')
            ->assertOk();

        $this->actingAs($officer)
            ->get('/officer/violations')
            ->assertRedirect(route('officer.motorists.index'));

        $this->actingAs($officer)
            ->get('/officer/incidents')
            ->assertOk();

        // Cannot access admin user management
        $this->actingAs($officer)
            ->get('/users')
            ->assertStatus(403);
    }

    /** 7. Records Officer Tests */
    public function test_records_officer_access(): void
    {
        $recordsOfficer = User::factory()->recordsOfficer()->create(['lgu_id' => 1]);

        $this->actingAs($recordsOfficer)
            ->get('/violators')
            ->assertOk();

        $this->actingAs($recordsOfficer)
            ->get('/violations')
            ->assertOk();

        $this->actingAs($recordsOfficer)
            ->get('/incidents')
            ->assertOk();

        $this->actingAs($recordsOfficer)
            ->get('/vehicles')
            ->assertOk();

        // Restricted from deleting records and managing users
        $this->actingAs($recordsOfficer)
            ->get('/users')
            ->assertStatus(403);

        $this->actingAs($recordsOfficer)
            ->get('/lgus')
            ->assertStatus(403);
    }

    /** 8. Auditor / View-Only User Tests */
    public function test_auditor_read_only_access_and_mutation_blocking(): void
    {
        $auditor = User::factory()->auditor()->create();

        // Read-only pages allowed
        $this->actingAs($auditor)
            ->get('/dashboard')
            ->assertOk();

        $this->actingAs($auditor)
            ->get('/reports')
            ->assertOk();

        $this->actingAs($auditor)
            ->get('/payments/report')
            ->assertOk();

        $this->actingAs($auditor)
            ->get('/audit-logs')
            ->assertOk();

        $this->actingAs($auditor)
            ->get('/violations')
            ->assertOk();

        $this->actingAs($auditor)
            ->get('/incidents')
            ->assertOk();

        // Mutation actions MUST be blocked (403 Forbidden)
        $this->actingAs($auditor)
            ->get('/violators/create')
            ->assertStatus(403);

        $this->actingAs($auditor)
            ->post('/violators', [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'license_number' => 'N01-12-345678',
            ])
            ->assertStatus(403);

        $this->actingAs($auditor)
            ->get('/users')
            ->assertStatus(403);
    }

    public function test_super_admin_can_only_distribute_top_three_admin_roles(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/users/create');
        $response->assertOk();
        $response->assertViewHas('roles', function ($roles) {
            return count($roles) === 3
                && isset($roles['admin'])
                && isset($roles['province_admin'])
                && isset($roles['operator'])
                && !isset($roles['cashier']);
        });

        // Attempting to assign restricted role should fail validation
        $this->actingAs($admin)
            ->post('/users', [
                'name' => 'Restricted Role User',
                'username' => 'restricted_cashier',
                'role' => 'cashier',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])
            ->assertSessionHasErrors(['role']);

        // Assigning valid administrative role should succeed
        $this->actingAs($admin)
            ->post('/users', [
                'name' => 'Valid Province Admin',
                'username' => 'valid_prov_admin',
                'role' => 'province_admin',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['username' => 'valid_prov_admin', 'role' => 'province_admin']);
    }
}
