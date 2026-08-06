<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lgus', function (Blueprint $table) {
            $table->string('police_chief_name')->nullable()->after('seal_path');
            $table->string('police_chief_title')->nullable()->after('police_chief_name');
        });
    }

    public function down(): void
    {
        Schema::table('lgus', function (Blueprint $table) {
            $table->dropColumn(['police_chief_name', 'police_chief_title']);
        });
    }
};
