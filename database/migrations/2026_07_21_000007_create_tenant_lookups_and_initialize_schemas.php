<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create tenant lookup table for fast cross-tenant guest/public routes resolution
        Schema::create('tenant_lookups', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('lgu_id');
            $table->timestamps();

            $table->unique(['type', 'model_id']);
        });

        // 2. Clone tables into the Balamban (BAL) tenant schema
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE SCHEMA IF NOT EXISTS lgu_bal');

            $tables = [
                'violators',
                'vehicles',
                'vehicle_photos',
                'violations',
                'violation_vehicle_photos',
                'incidents',
                'incident_motorists',
                'incident_media',
                'payments',
            ];

            foreach ($tables as $table) {
                // Duplicate structure, indexes, and defaults
                DB::statement("CREATE TABLE IF NOT EXISTS lgu_bal.{$table} (LIKE public.{$table} INCLUDING ALL)");

                // Copy existing data from public to lgu_bal
                DB::statement("INSERT INTO lgu_bal.{$table} SELECT * FROM public.{$table}");

                // Backfill lookup entries for existing tenant records
                if (in_array($table, ['violations', 'incidents', 'violators', 'vehicles'])) {
                    $rows = DB::table("public.{$table}")->get();
                    $type = Str::singular($table);
                    foreach ($rows as $row) {
                        $lguId = $row->lgu_id ?? 1; // Default to first LGU (Balamban)
                        DB::table('tenant_lookups')->insertOrIgnore([
                            'type'       => $type,
                            'model_id'   => $row->id,
                            'lgu_id'     => $lguId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // Reset PostgreSQL serial id sequences in lgu_bal to match current max ids
                DB::statement("
                    SELECT setval(
                        pg_get_serial_sequence('lgu_bal.{$table}', 'id'),
                        COALESCE((SELECT MAX(id) FROM lgu_bal.{$table}), 1),
                        true
                    )
                ");
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_lookups');

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP SCHEMA IF EXISTS lgu_bal CASCADE');
        }
    }
};
