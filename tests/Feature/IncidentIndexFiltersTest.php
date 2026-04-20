<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IncidentIndexFiltersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('incident_media');
        Schema::dropIfExists('incident_motorists');
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('violators');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->nullable();
            $table->string('role')->default('operator');
            $table->string('password');
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('violators', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('license_number')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('incident_number')->unique();
            $table->date('date_of_incident');
            $table->time('time_of_incident')->nullable();
            $table->string('location');
            $table->text('description')->nullable();
            $table->string('status')->default('under_investigation');
            $table->unsignedBigInteger('recorded_by');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('incident_motorists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('incident_id');
            $table->unsignedBigInteger('violator_id')->nullable();
            $table->string('motorist_name')->nullable();
            $table->timestamps();
        });

        Schema::create('incident_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('incident_id');
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    private function makeUser(string $role): User
    {
        $id = DB::table('users')->insertGetId([
            'name'       => ucfirst(str_replace('_', ' ', $role)) . ' User',
            'username'   => $role . '_user',
            'email'      => $role . '@example.com',
            'role'       => $role,
            'password'   => bcrypt('secret'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::query()->findOrFail($id);
    }

    private function makeIncident(int $recorderId, array $overrides = []): void
    {
        DB::table('incidents')->insert(array_merge([
            'incident_number'   => 'INC-2026-0001',
            'recorded_by'       => $recorderId,
            'date_of_incident'  => '2026-04-20',
            'location'          => 'Balamban Highway',
            'status'            => 'under_investigation',
            'created_at'        => now(),
            'updated_at'        => now(),
        ], $overrides));
    }

    public function test_operator_incidents_index_handles_blank_filter_values(): void
    {
        $operator = $this->makeUser('operator');
        $this->makeIncident($operator->id);

        $response = $this->actingAs($operator)
            ->get(route('incidents.index') . '?search=Balamban&date_from=&date_to=&status=');

        $response->assertOk();
        $response->assertSeeText('Balamban Highway');
    }

    public function test_operator_incidents_index_ignores_invalid_date_filters(): void
    {
        $operator = $this->makeUser('operator');
        $this->makeIncident($operator->id, [
            'incident_number' => 'INC-2026-0002',
            'location'        => 'Cebu Transcentral',
        ]);

        $response = $this->actingAs($operator)
            ->get(route('incidents.index') . '?date_from=not-a-date&date_to=2026-99-99');

        $response->assertOk();
        $response->assertSeeText('Cebu Transcentral');
    }

    public function test_officer_incidents_index_handles_blank_status_filter(): void
    {
        $officer = $this->makeUser('traffic_officer');
        $this->makeIncident($officer->id, [
            'incident_number' => 'INC-2026-0003',
            'location'        => 'Officer Route Test',
        ]);

        $response = $this->actingAs($officer)
            ->get(route('officer.incidents.index') . '?search=Officer&status=');

        $response->assertOk();
        $response->assertSeeText('Officer Route Test');
    }
}
