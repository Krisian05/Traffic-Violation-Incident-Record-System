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

    public function test_lgu_admin_is_scoped_to_own_lgu_audit_logs(): void
    {
        $lgu1 = \App\Models\Lgu::factory()->create();
        $lgu2 = \App\Models\Lgu::factory()->create();

        $lgu1Admin = User::factory()->create([
            'role'   => 'operator',
            'lgu_id' => $lgu1->id,
        ]);

        $lgu2Admin = User::factory()->create([
            'role'   => 'operator',
            'lgu_id' => $lgu2->id,
        ]);

        Activity::create([
            'log_name'    => 'default',
            'description' => 'LGU 1 Action Log Entry',
            'event'       => 'created',
            'causer_id'   => $lgu1Admin->id,
            'causer_type' => User::class,
        ]);

        Activity::create([
            'log_name'    => 'default',
            'description' => 'LGU 2 Action Log Entry',
            'event'       => 'created',
            'causer_id'   => $lgu2Admin->id,
            'causer_type' => User::class,
        ]);

        $response = $this->actingAs($lgu1Admin)->get(route('audit-logs.index'));

        $response->assertStatus(200);
        $response->assertSee('LGU 1 Action Log Entry');
        $response->assertDontSee('LGU 2 Action Log Entry');
    }

    public function test_super_admin_can_view_all_and_filter_audit_logs_by_lgu(): void
    {
        $lgu1 = \App\Models\Lgu::factory()->create();
        $lgu2 = \App\Models\Lgu::factory()->create();

        $superAdmin = User::factory()->create([
            'role'   => 'admin',
            'lgu_id' => null,
        ]);

        $lgu1Admin = User::factory()->create([
            'role'   => 'operator',
            'lgu_id' => $lgu1->id,
        ]);

        $lgu2Admin = User::factory()->create([
            'role'   => 'operator',
            'lgu_id' => $lgu2->id,
        ]);

        Activity::create([
            'log_name'    => 'default',
            'description' => 'Global Log Entry for LGU 1',
            'event'       => 'created',
            'causer_id'   => $lgu1Admin->id,
            'causer_type' => User::class,
        ]);

        Activity::create([
            'log_name'    => 'default',
            'description' => 'Global Log Entry for LGU 2',
            'event'       => 'created',
            'causer_id'   => $lgu2Admin->id,
            'causer_type' => User::class,
        ]);

        // Super Admin without filter sees both
        $responseAll = $this->actingAs($superAdmin)->get(route('audit-logs.index'));
        $responseAll->assertStatus(200);
        $responseAll->assertSee('Global Log Entry for LGU 1');
        $responseAll->assertSee('Global Log Entry for LGU 2');

        // Super Admin filtering by LGU 1 sees only LGU 1
        $responseFiltered = $this->actingAs($superAdmin)->get(route('audit-logs.index', ['lgu_id' => $lgu1->id]));
        $responseFiltered->assertStatus(200);
        $responseFiltered->assertSee('Global Log Entry for LGU 1');
        $responseFiltered->assertDontSee('Global Log Entry for LGU 2');
    }
}
