<?php

namespace Tests\Feature;

use App\Models\Lgu;
use App\Models\User;
use App\Models\Violation;
use App\Models\Violator;
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

    public function test_dsr_submission_succeeds_without_email(): void
    {
        $response = $this->post('/privacy/data-subject-request', [
            'full_name'      => 'Maria Santos',
            'email'          => null,
            'contact_number' => '09189998888',
            'license_number' => 'N02-99-123456',
            'ticket_number'  => 'TVIRS-CEB-BAL-2026-000002',
            'request_type'   => 'correction',
            'details'        => 'Requesting update to my contact number.',
        ]);

        $response->assertRedirect('/privacy/data-subject-request');
        $response->assertSessionHas('success');
    }

    public function test_motorist_search_autocomplete_scopes_by_lgu_for_cashier(): void
    {
        $lgu1 = Lgu::factory()->create(['name' => 'Balamban']);
        $lgu2 = Lgu::factory()->create(['name' => 'Barili']);

        $cashierBalamban = User::factory()->create(['role' => 'cashier', 'lgu_id' => $lgu1->id]);
        $globalAdmin     = User::factory()->create(['role' => 'admin', 'lgu_id' => null]);

        $violatorBalamban = Violator::factory()->create([
            'first_name'     => 'Juan',
            'last_name'      => 'Dela Cruz',
            'contact_number' => '09171111111',
            'license_number' => 'N01-11-111111',
            'lgu_id'         => $lgu1->id,
        ]);

        $violatorBarili = Violator::factory()->create([
            'first_name'     => 'Juanito',
            'last_name'      => 'Reyes',
            'contact_number' => '09172222222',
            'license_number' => 'N02-22-222222',
            'lgu_id'         => $lgu2->id,
        ]);

        // Cashier from Balamban searching "Juan" should ONLY see Balamban violator
        $response = $this->actingAs($cashierBalamban)->getJson('/privacy/search-motorists?q=Juan');
        $response->assertStatus(200);
        $response->assertJsonFragment(['full_name' => 'Juan Dela Cruz']);
        $response->assertJsonMissing(['full_name' => 'Juanito Reyes']);

        // Global admin searching "Juan" should see BOTH violators across LGUs
        $responseAdmin = $this->actingAs($globalAdmin)->getJson('/privacy/search-motorists?q=Juan');
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertJsonFragment(['full_name' => 'Juan Dela Cruz']);
        $responseAdmin->assertJsonFragment(['full_name' => 'Juanito Reyes']);
    }

    public function test_data_retention_cleanup_command_executes_successfully(): void
    {
        $exitCode = Artisan::call('tvirs:retention-cleanup');
        $this->assertSame(0, $exitCode);
    }
}
