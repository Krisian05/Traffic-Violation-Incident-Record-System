<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ViolationType;

return new class extends Migration
{
    public function up(): void
    {
        $tablesToClear = [
            'payments',
            'payment_claims',
            'online_payment_sessions',
            'violation_vehicle_photos',
            'violations',
            'incident_status_histories',
            'incident_parties',
            'incident_motorists',
            'incident_media',
            'incidents',
            'vehicle_photos',
            'vehicles',
            'violators',
            'notifications',
            'activity_log',
        ];

        foreach ($tablesToClear as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        // Delete all global default types
        ViolationType::whereNull('lgu_id')->delete();
    }

    public function down(): void
    {
        // No roll back
    }
};
