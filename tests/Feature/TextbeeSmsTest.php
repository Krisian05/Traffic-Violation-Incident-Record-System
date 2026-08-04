<?php

namespace Tests\Feature;

use App\Models\Lgu;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\Violator;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TextbeeSmsTest extends TestCase
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
            'sms_provider' => 'textbee',
            'textbee_api_key' => 'tb_key_test_12345',
            'textbee_device_id' => 'dev_test_67890',
            'sms_auto_send' => true,
        ]);

        $this->user = User::factory()->create([
            'role' => 'admin',
            'lgu_id' => $this->lgu->id,
        ]);
    }

    public function test_textbee_sim_gateway_dispatches_http_request(): void
    {
        Http::fake([
            'https://api.textbee.dev/api/v1/gateway/devices/dev_test_67890/send-sms' => Http::response(['status' => 'success'], 200),
        ]);

        $violator = Violator::create([
            'first_name' => 'Pedro',
            'last_name' => 'Penduko',
            'contact_number' => '09171112222',
        ]);

        $type = ViolationType::create([
            'name' => 'Illegal Parking',
            'fine_amount' => 500,
        ]);

        $violation = Violation::create([
            'violator_id' => $violator->id,
            'violation_type_id' => $type->id,
            'lgu_id' => $this->lgu->id,
            'ticket_number' => 'TVIRS-CEB-BAL-2026-999999',
            'date_of_violation' => now()->toDateString(),
            'status' => 'pending',
            'recorded_by' => $this->user->id,
        ]);

        $smsService = new SmsService();
        $result = $smsService->sendCitationSms($violation);

        $this->assertTrue($result['success']);
        $this->assertEquals('sent', $violation->fresh()->sms_status);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.textbee.dev/api/v1/gateway/devices/dev_test_67890/send-sms' &&
                   $request->hasHeader('x-api-key', 'tb_key_test_12345') &&
                   $request['recipients'][0] === '+639171112222';
        });
    }

    public function test_updating_sms_gateway_settings(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('sms.settings'), [
                'lgu_id'            => $this->lgu->id,
                'sms_provider'      => 'textbee',
                'textbee_api_key'   => 'new_api_key_abc',
                'textbee_device_id' => 'new_device_xyz',
                'sms_auto_send'     => '1',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('lgus', [
            'id'                => $this->lgu->id,
            'sms_provider'      => 'textbee',
            'textbee_api_key'   => 'new_api_key_abc',
            'textbee_device_id' => 'new_device_xyz',
        ]);
    }
}
