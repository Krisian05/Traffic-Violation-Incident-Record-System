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

        // System administration (LGUs access)
        $this->actingAs($provinceAdmin)
            ->get('/lgus')
            ->assertOk();
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

        // Can view assigned LGU
        $this->actingAs($lguAdmin)
            ->get('/lgus')
            ->assertOk();

        // Cannot create new LGU or edit other LGUs
        $this->actingAs($lguAdmin)
            ->get('/lgus/create')
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

        $this->actingAs($supervisor)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Field Personnel &amp; Citation Oversight', false);

        $this->actingAs($supervisor)
            ->get('/audit-logs')
            ->assertOk()
            ->assertSee('Traffic enforcement, citation tickets &amp; field officer activity logs', false);

        $this->actingAs($supervisor)
            ->get('/violators/create')
            ->assertOk();

        $this->actingAs($supervisor)
            ->get('/incidents/create')
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

    public function test_lgu_admin_creates_user_locked_to_own_lgu_and_super_admin_sees_dropdown(): void
    {
        $lguAdmin = User::factory()->lguAdmin()->create(['lgu_id' => 1]);
        $superAdmin = User::factory()->superAdmin()->create();

        // 1. LGU Admin view shows locked input note
        $responseLgu = $this->actingAs($lguAdmin)->get('/users/create');
        $responseLgu->assertOk();
        $responseLgu->assertSee('Accounts created by LGU Administrators are automatically assigned to your LGU.');

        // LGU Admin posting user automatically assigns LGU id 1
        $this->actingAs($lguAdmin)
            ->post('/users', [
                'name' => 'LGU Cashier',
                'username' => 'lgu_cashier_balamban',
                'role' => 'cashier',
                'lgu_id' => 999, // Attempts to set another LGU, but should be overridden
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'username' => 'lgu_cashier_balamban',
            'lgu_id' => 1,
        ]);

        // 2. Super Admin view shows full dropdown text hint
        $responseSuper = $this->actingAs($superAdmin)->get('/users/create');
        $responseSuper->assertOk();
        $responseSuper->assertSee('Only needed for accounts that should be scoped to a single LGU.');
    }

    public function test_lgu_admin_dashboard_card_scoping_global_motorist_visibility_and_cross_lgu_record_protection(): void
    {
        $lgu2 = Lgu::factory()->create(['id' => 2, 'name' => 'Balamban', 'code' => 'BAL']);

        $lguAdmin1 = User::factory()->lguAdmin()->create(['lgu_id' => 1]);
        $lguAdmin2 = User::factory()->lguAdmin()->create(['lgu_id' => 2]);

        $motorist1 = Violator::factory()->create(['lgu_id' => 1, 'first_name' => 'Alpha', 'middle_name' => null, 'last_name' => 'One']);
        $motorist2 = Violator::factory()->create(['lgu_id' => 2, 'first_name' => 'Beta', 'middle_name' => null, 'last_name' => 'Two']);

        $violation1 = Violation::factory()->create(['lgu_id' => 1, 'violator_id' => $motorist1->id, 'recorded_by' => $lguAdmin1->id, 'date_of_violation' => now()->toDateString()]);
        $violation2 = Violation::factory()->create(['lgu_id' => 2, 'violator_id' => $motorist2->id, 'recorded_by' => $lguAdmin2->id, 'date_of_violation' => now()->toDateString()]);

        $incident1 = Incident::factory()->create(['lgu_id' => 1, 'recorded_by' => $lguAdmin1->id, 'date_of_incident' => now()->toDateString()]);
        $incident2 = Incident::factory()->create(['lgu_id' => 2, 'recorded_by' => $lguAdmin2->id, 'date_of_incident' => now()->toDateString()]);

        // 1. Dashboard Scoping: LGU Admin 2 sees only LGU 2 counts on dashboard
        $response = $this->actingAs($lguAdmin2)->get('/dashboard');
        $response->assertOk();
        $response->assertViewHas('totalViolators', 1);
        $response->assertViewHas('violationsThisMonth', 1);
        $response->assertViewHas('incidentsThisMonth', 1);

        // 2. Global Motorist Directory: LGU Admin 2 can view both motorists
        $responseMotorists = $this->actingAs($lguAdmin2)->get('/violators');
        $responseMotorists->assertOk();
        $responseMotorists->assertSee('Alpha One');
        $responseMotorists->assertSee('Beta Two');

        // 3. Cross-LGU Read Access: LGU Admin 2 CAN view Violation 1 and Incident 1 (LGU 1)
        $this->actingAs($lguAdmin2)->get("/violations/{$violation1->id}")->assertOk();
        $this->actingAs($lguAdmin2)->get("/incidents/{$incident1->id}")->assertOk();

        // 4. Cross-LGU Protection (No-Touch Rule): LGU Admin 2 CANNOT edit or delete Violation 1 or Incident 1 (LGU 1)
        $this->actingAs($lguAdmin2)->get("/violations/{$violation1->id}/edit")->assertStatus(403);
        $this->actingAs($lguAdmin2)->delete("/violations/{$violation1->id}")->assertStatus(403);

        $this->actingAs($lguAdmin2)->get("/incidents/{$incident1->id}/edit")->assertStatus(403);
        $this->actingAs($lguAdmin2)->delete("/incidents/{$incident1->id}")->assertStatus(403);

        // LGU Admin 2 CAN edit own LGU violation and incident
        $this->actingAs($lguAdmin2)->get("/violations/{$violation2->id}/edit")->assertOk();
        $this->actingAs($lguAdmin2)->get("/incidents/{$incident2->id}/edit")->assertOk();
    }

    public function test_login_clears_unauthorized_intended_url_to_prevent_403(): void
    {
        $lguAdmin = User::factory()->lguAdmin()->create(['password' => bcrypt('password123')]);

        // Simulate visiting an admin-only page while unauthenticated
        session(['url.intended' => 'http://localhost/lgus']);

        $response = $this->post('/login', [
            'username' => $lguAdmin->username,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($lguAdmin);

        // Accessing dashboard should return 200 OK
        $this->get('/dashboard')->assertOk();
    }
}
