<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violators', function (Blueprint $table) {
            $table->foreignId('lgu_id')->nullable()->after('id')->constrained('lgus')->nullOnDelete();
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('lgu_id')->nullable()->after('id')->constrained('lgus')->nullOnDelete();
        });

        // Only one LGU has ever been onboarded (Balamban) — no ambiguity in backfilling
        // every existing violator/vehicle to it, same reasoning as the earlier
        // violations/incidents backfill.
        $baLguId = DB::table('lgus')->where('code', 'BAL')->value('id');

        if ($baLguId) {
            DB::table('violators')->whereNull('lgu_id')->update(['lgu_id' => $baLguId]);
            DB::table('vehicles')->whereNull('lgu_id')->update(['lgu_id' => $baLguId]);
        }
    }

    public function down(): void
    {
        Schema::table('violators', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lgu_id');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lgu_id');
        });
    }
};
