<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Activitylog\Models\Activity;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_audit_logs(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Activity::create([
            'log_name' => 'default',
            'description' => 'Test admin action performed',
            'event' => 'created',
            'causer_id' => $admin->id,
            'causer_type' => User::class,
        ]);

        $response = $this->actingAs($admin)->get(route('audit-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('Test admin action performed');
    }
}
