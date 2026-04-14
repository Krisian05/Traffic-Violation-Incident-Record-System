<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incident_motorists', function (Blueprint $table) {
            // varchar(255) is too short for a JSON array of 4 photo paths
            $table->text('vehicle_photo')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('incident_motorists', function (Blueprint $table) {
            $table->string('vehicle_photo')->nullable()->change();
        });
    }
};
