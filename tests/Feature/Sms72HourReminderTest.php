<?php

namespace Tests\Feature;

use App\Models\Lgu;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Violator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sms72HourReminderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Lgu $lgu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lgu = Lgu::create([
            'code' => 'BAL',
            'name' => 'Balamban',
            'province' => 'Cebu',
            'sms_provider' => 'local',
            'sms_auto_send' => true,
        ]);

        $this->user = User::factory()->create([
            'role' => 'admin',
            'lgu_id' => $this->lgu->id,
        ]);
    }

    public function test_72_hour_reminder_command_sends_sms_to_eligible_violations(): void
    {
        $violator = Violator::create([
            'first_name' => 'Cardo',
            'last_name' => 'Dalisay',
            'contact_number' => '09170001111',
        ]);

        $type = ViolationType::create([
            'name' => 'Over-speeding',
            'penalty_amount' => 1500,
        ]);

        $oldViolation = Violation::create([
            'violator_id' => $violator->id,
            'violation_type_id' => $type->id,
            'lgu_id' => $this->lgu->id,
            'ticket_number' => 'TVIRS-CEB-BAL-2026-720001',
            'date_of_violation' => now()->subDays(4)->toDateString(),
            'status' => 'pending',
            'recorded_by' => $this->user->id,
        ]);
        \Illuminate\Support\Facades\DB::table('violations')->where('id', $oldViolation->id)->update(['created_at' => now()->subHours(75)]);

        // Violation issued 10 hours ago (NOT eligible yet)
        $recentViolation = Violation::create([
            'violator_id' => $violator->id,
            'violation_type_id' => $type->id,
            'lgu_id' => $this->lgu->id,
            'ticket_number' => 'TVIRS-CEB-BAL-2026-720002',
            'date_of_violation' => now()->toDateString(),
            'status' => 'pending',
            'recorded_by' => $this->user->id,
            'created_at' => now()->subHours(10),
        ]);

        $this->artisan('sms:send-72h-reminders')
            ->assertExitCode(0);

        $this->assertNotNull($oldViolation->fresh()->sms_reminder_sent_at);
        $this->assertNull($recentViolation->fresh()->sms_reminder_sent_at);
    }
}
