<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lgus', function (Blueprint $table) {
            $table->string('police_station_name')->nullable()->after('treasurer_office');
            $table->string('police_station_address')->nullable()->after('police_station_name');
            $table->string('seal_path')->nullable()->after('police_station_address');
        });
    }

    public function down(): void
    {
        Schema::table('lgus', function (Blueprint $table) {
            $table->dropColumn(['police_station_name', 'police_station_address', 'seal_path']);
        });
    }
};
