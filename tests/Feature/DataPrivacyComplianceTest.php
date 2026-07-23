<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class DataPrivacyComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_policy_page_is_accessible(): void
    {
        $response = $this->get('/privacy-policy');

        $response->assertStatus(200);
        $response->assertSee('Data Privacy Compliance');
        $response->assertSee('Republic Act No. 10173');
        $response->assertSee('National Privacy Commission');
    }

    public function test_data_subject_request_form_is_accessible(): void
    {
        $response = $this->get('/privacy/data-subject-request');

        $response->assertStatus(200);
        $response->assertSee('Data Subject Request (DSR) Portal');
    }

    public function test_data_subject_request_submission_logs_activity(): void
    {
        $response = $this->post('/privacy/data-subject-request', [
            'full_name'      => 'Juan Dela Cruz',
            'email'          => 'juan@example.com',
            'contact_number' => '09171234567',
            'license_number' => 'A01-12-345678',
            'ticket_number'  => 'TVIRS-CEB-BAL-2026-000001',
            'request_type'   => 'access',
            'details'        => 'Requesting a copy of all citation records attached to my driver license.',
        ]);

        $response->assertRedirect('/privacy/data-subject-request');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'privacy',
            'event'    => 'data_subject_request',
        ]);
    }

    public function test_data_retention_cleanup_command_executes_successfully(): void
    {
        $exitCode = Artisan::call('tvirs:retention-cleanup');
        $this->assertSame(0, $exitCode);
    }
}
