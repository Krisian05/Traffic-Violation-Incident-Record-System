<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Violator;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_feed_api_returns_user_notifications(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        Notification::create([
            'id'              => 'test-uuid-1',
            'type'            => 'violation_created',
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'data'            => [
                'title'   => 'New Violation',
                'message' => 'Ticket TVIRS-001 created',
                'icon'    => 'bi-ticket-perforated-fill',
                'color'   => '#dc2626',
            ],
        ]);

        $response = $this->actingAs($user)->getJson('/notifications/feed');

        $response->assertStatus(200);
        $response->assertJson([
            'unread_count' => 1,
        ]);
        $response->assertJsonFragment([
            'title' => 'New Violation',
        ]);
    }

    public function test_mark_as_read_updates_read_at_timestamp(): void
    {
        $user = User::factory()->create(['role' => 'cashier']);

        $notif = Notification::create([
            'id'              => 'test-uuid-2',
            'type'            => 'payment_settled',
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'data'            => [
                'title' => 'Payment Settled',
            ],
        ]);

        $response = $this->actingAs($user)->postJson("/notifications/{$notif->id}/read");

        $response->assertStatus(200);
        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_mark_all_read_clears_all_unread_for_user(): void
    {
        $user = User::factory()->create(['role' => 'treasurer']);

        Notification::create([
            'id'              => 'test-uuid-3',
            'type'            => 'dsr_submitted',
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'data'            => ['title' => 'DSR 1'],
        ]);

        Notification::create([
            'id'              => 'test-uuid-4',
            'type'            => 'dsr_submitted',
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'data'            => ['title' => 'DSR 2'],
        ]);

        $response = $this->actingAs($user)->postJson('/notifications/read-all');

        $response->assertStatus(200);
        $this->assertEquals(0, Notification::where('notifiable_id', $user->id)->whereNull('read_at')->count());
    }

    public function test_dsr_submission_triggers_notification(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->post('/privacy/data-subject-request', [
            'full_name'      => 'Patricia Dizon',
            'email'          => 'patricia@example.com',
            'contact_number' => '096452734889',
            'license_number' => 'P51-21-942397',
            'ticket_number'  => 'TVIRS-CEB-BAL-2026-000001',
            'request_type'   => 'correction',
            'details'        => 'Requesting data correction',
        ]);

        $this->assertDatabaseHas('notifications', [
            'type'          => 'dsr_submitted',
            'notifiable_id' => $admin->id,
        ]);
    }
}
