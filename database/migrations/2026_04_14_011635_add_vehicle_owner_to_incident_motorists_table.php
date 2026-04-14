<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_motorists', function (Blueprint $table) {
            // If the driver is NOT the vehicle owner, capture owner info separately
            $table->foreignId('vehicle_owner_violator_id')->nullable()->after('vehicle_chassis')
                  ->constrained('violators')->nullOnDelete();
            $table->string('vehicle_owner_name')->nullable()->after('vehicle_owner_violator_id');
            $table->string('vehicle_owner_contact')->nullable()->after('vehicle_owner_name');
        });
    }

    public function down(): void
    {
        Schema::table('incident_motorists', function (Blueprint $table) {
            $table->dropForeign(['vehicle_owner_violator_id']);
            $table->dropColumn(['vehicle_owner_violator_id', 'vehicle_owner_name', 'vehicle_owner_contact']);
        });
    }
};
