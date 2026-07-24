<?php

namespace Tests\Feature;

use App\Models\Lgu;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SuperAdminPrivilegesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Lgu::factory()->create(['id' => 1, 'name' => 'Cebu City', 'code' => 'CEB']);
    }

    /** 1. Test Super Admin Screen Access across all 5 Pillars */
    public function test_super_admin_can_access_all_5_super_admin_suite_screens(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $routes = [
            '/admin/system-config',
            '/admin/security',
            '/admin/security/active-sessions',
            '/admin/monitoring',
            '/admin/monitoring/api/stats',
            '/admin/technical',
            '/admin/technical/logs',
            '/admin/technical/backups',
        ];

        foreach ($routes as $route) {
            $this->actingAs($superAdmin)
                ->get($route)
                ->assertOk();
        }
    }

    /** 2. Test Overall System Configuration Updates */
    public function test_super_admin_can_update_system_configuration(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = [
            'system_name'              => 'Updated TVIRS Suite Title',
            'system_short_name'        => 'TVIRS-PROV',
            'support_email'            => 'admin@tvirs.gov.ph',
            'support_phone'            => '09170001122',
            'default_grace_period_days'=> '20',
            'late_penalty_rate'        => '15.50',
            'auto_due_date_enabled'    => '1',
            'ocr_enabled'              => '1',
            'ocr_primary_engine'       => 'ocr_space',
            'ocr_confidence_min'       => '80',
            'online_payments_enabled'  => '1',
            'receipt_prefix'           => 'OR-CEB-',
            'enforce_2fa_admin'        => '1',
            'session_timeout_minutes'  => '60',
            'max_login_attempts'       => '3',
            'lockout_duration_minutes' => '30',
            'maintenance_mode'         => '0',
            'maintenance_message'      => 'System undergoing scheduled maintenance.',
            'backup_retention_days'    => '45',
        ];

        $this->actingAs($superAdmin)
            ->post('/admin/system-config', $payload)
            ->assertRedirect('/admin/system-config')
            ->assertSessionHas('success');

        $this->assertEquals('Updated TVIRS Suite Title', SystemSetting::get('system_name'));
        $this->assertEquals(20, SystemSetting::get('default_grace_period_days'));
        $this->assertEquals('ocr_space', SystemSetting::get('ocr_primary_engine'));
    }

    /** 3. Test User Status Toggle, Password Reset, and Session Revocation */
    public function test_super_admin_user_management_actions(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $targetUser = User::factory()->create([
            'status'   => 'active',
            'username' => 'field_officer_test',
        ]);

        // Toggle Status -> Inactive
        $this->actingAs($superAdmin)
            ->post("/users/{$targetUser->id}/toggle-status")
            ->assertStatus(302);

        $this->assertEquals('inactive', $targetUser->fresh()->status);

        // Toggle Status back to Active
        $this->actingAs($superAdmin)
            ->post("/users/{$targetUser->id}/toggle-status")
            ->assertStatus(302);

        $this->assertEquals('active', $targetUser->fresh()->status);

        // Reset Password
        $this->actingAs($superAdmin)
            ->post("/users/{$targetUser->id}/reset-password", [
                'password'              => 'NewSecurePass123!',
                'password_confirmation' => 'NewSecurePass123!',
            ])
            ->assertStatus(302)
            ->assertSessionHas('success');

        // Revoke Sessions
        $this->actingAs($superAdmin)
            ->post("/users/{$targetUser->id}/revoke-sessions")
            ->assertStatus(302)
            ->assertSessionHas('success');
    }

    /** 4. Test Security Policy Updates */
    public function test_super_admin_can_update_security_policies(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $payload = [
            'enforce_2fa_admin'        => '1',
            'session_timeout_minutes'  => '90',
            'max_login_attempts'       => '4',
            'lockout_duration_minutes' => '20',
        ];

        $this->actingAs($superAdmin)
            ->post('/admin/security/policy', $payload)
            ->assertRedirect('/admin/security')
            ->assertSessionHas('success');

        $this->assertEquals(true, SystemSetting::get('enforce_2fa_admin'));
        $this->assertEquals(90, SystemSetting::get('session_timeout_minutes'));
    }

    /** 5. Test Database Backup Snapshot Lifecycle */
    public function test_super_admin_can_create_download_and_delete_backups(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        // Create Backup
        $this->actingAs($superAdmin)
            ->post('/admin/technical/backups/create')
            ->assertRedirect('/admin/technical/backups')
            ->assertSessionHas('success');

        $backupsDir = storage_path('app/backups');
        $files = File::files($backupsDir);
        $this->assertNotEmpty($files);

        $latestBackup = basename($files[0]->getFilename());

        // Download Backup
        $this->actingAs($superAdmin)
            ->get("/admin/technical/backups/{$latestBackup}/download")
            ->assertOk();

        // Delete Backup
        $this->actingAs($superAdmin)
            ->delete("/admin/technical/backups/{$latestBackup}")
            ->assertStatus(302)
            ->assertSessionHas('success');
    }

    /** 6. Test Non-Super Admin Access Control Blocking */
    public function test_non_super_admin_cannot_access_super_admin_suite(): void
    {
        $operator = User::factory()->operator()->create();
        $officer = User::factory()->trafficOfficer()->create();
        $cashier = User::factory()->cashier()->create();

        $routes = [
            '/admin/system-config',
            '/admin/security',
            '/admin/monitoring',
            '/admin/technical',
            '/admin/technical/logs',
            '/admin/technical/backups',
        ];

        foreach ([$operator, $officer, $cashier] as $user) {
            foreach ($routes as $route) {
                $this->actingAs($user)
                    ->get($route)
                    ->assertStatus(403);
            }
        }
    }
}
