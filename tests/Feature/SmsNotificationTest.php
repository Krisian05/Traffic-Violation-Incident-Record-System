<?php

namespace Tests\Feature;

use App\Models\Lgu;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Violator;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsNotificationTest extends TestCase
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
            'sms_sender_name' => 'TVIRS',
            'sms_auto_send' => true,
        ]);

        $this->user = User::factory()->create([
            'role' => 'admin',
            'lgu_id' => $this->lgu->id,
        ]);
    }

    public function test_sms_service_logs_when_no_api_key(): void
    {
        $violator = Violator::create([
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'contact_number' => '09171234567',
        ]);

        $type = ViolationType::create([
            'name' => 'No Helmet',
            'penalty_amount' => 500,
        ]);

        $violation = Violation::create([
            'violator_id' => $violator->id,
            'violation_type_id' => $type->id,
            'lgu_id' => $this->lgu->id,
            'ticket_number' => 'TVIRS-CEB-BAL-2026-000001',
            'date_of_violation' => now()->toDateString(),
            'status' => 'pending',
            'recorded_by' => $this->user->id,
        ]);

        $smsService = new SmsService();
        $result = $smsService->sendCitationSms($violation);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('violations', [
            'id' => $violation->id,
            'sms_status' => 'sent',
        ]);
    }

    public function test_manual_sms_endpoint_triggers_dispatch(): void
    {
        $violator = Violator::create([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'contact_number' => '09189876543',
        ]);

        $type = ViolationType::create([
            'name' => 'Reckless Driving',
            'penalty_amount' => 1000,
        ]);

        $violation = Violation::create([
            'violator_id' => $violator->id,
            'violation_type_id' => $type->id,
            'lgu_id' => $this->lgu->id,
            'ticket_number' => 'TVIRS-CEB-BAL-2026-000002',
            'date_of_violation' => now()->toDateString(),
            'status' => 'pending',
            'recorded_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('violations.send-sms', $violation));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('sent', $violation->fresh()->sms_status);
    }
}
