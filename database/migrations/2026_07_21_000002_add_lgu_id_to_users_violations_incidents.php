<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('lgu_id')->nullable()->after('role')->constrained('lgus')->restrictOnDelete();
        });

        Schema::table('violations', function (Blueprint $table) {
            $table->foreignId('lgu_id')->nullable()->after('violator_id')->constrained('lgus')->restrictOnDelete();
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->foreignId('lgu_id')->nullable()->after('incident_number')->constrained('lgus')->restrictOnDelete();
        });

        // Every existing row predates multi-LGU support and was recorded by the
        // Balamban station (the letterhead hardcoded across the print views today).
        // Seed that LGU here (not only in the seeder) so this migration is safe to
        // run standalone against existing production data.
        $defaultLguId = DB::table('lgus')->where('code', 'BAL')->value('id');
        if (!$defaultLguId) {
            $defaultLguId = DB::table('lgus')->insertGetId([
                'code'       => 'BAL',
                'name'       => 'Balamban',
                'province'   => 'Cebu',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('users')->whereNull('lgu_id')->update(['lgu_id' => $defaultLguId]);
        DB::table('violations')->whereNull('lgu_id')->update(['lgu_id' => $defaultLguId]);
        DB::table('incidents')->whereNull('lgu_id')->update(['lgu_id' => $defaultLguId]);

        Schema::table('violations', function (Blueprint $table) {
            $table->foreignId('lgu_id')->nullable(false)->change();
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->foreignId('lgu_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->dropForeign(['lgu_id']);
            $table->dropColumn('lgu_id');
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->dropForeign(['lgu_id']);
            $table->dropColumn('lgu_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['lgu_id']);
            $table->dropColumn('lgu_id');
        });
    }
};
