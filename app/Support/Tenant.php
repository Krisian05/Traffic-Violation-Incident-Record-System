<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Tenant
{
    /**
     * Switch search_path to target LGU schema, falling back to public.
     */
    public static function switchTo(?string $lguCode): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        if (empty($lguCode)) {
            DB::statement('SET search_path TO public');
            return;
        }

        $schema = 'lgu_' . strtolower($lguCode);
        DB::statement("SET search_path TO {$schema}, public");
    }

    /**
     * Switch search_path to the schema that owns a specific model record.
     */
    public static function switchToModel(string $type, $id): void
    {
        if (DB::getDriverName() !== 'pgsql' || empty($id)) {
            return;
        }

        $lookup = DB::table('tenant_lookups')
            ->where('type', $type)
            ->where('model_id', $id)
            ->first();

        if ($lookup) {
            $lgu = DB::table('lgus')->where('id', $lookup->lgu_id)->first();
            if ($lgu) {
                self::switchTo($lgu->code);
                return;
            }
        }

        self::switchTo(null);
    }

    /**
     * Create schema and clone standard tables for a new LGU.
     */
    public static function createTenant(string $lguCode): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $schema = 'lgu_' . strtolower($lguCode);
        DB::statement("CREATE SCHEMA IF NOT EXISTS {$schema}");

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
            // Duplicate structure, defaults, and indexes of landlord/public template tables
            DB::statement("CREATE TABLE IF NOT EXISTS {$schema}.{$table} (LIKE public.{$table} INCLUDING ALL)");

            // Align auto-increment primary key serial sequences
            DB::statement("
                SELECT setval(
                    pg_get_serial_sequence('{$schema}.{$table}', 'id'),
                    COALESCE((SELECT MAX(id) FROM {$schema}.{$table}), 1),
                    true
                )
            ");
        }

        Log::info("Provisioned database schema: {$schema}");
    }

    /**
     * Register a tenant lookup entry for guest view resolution.
     */
    public static function registerLookup(string $type, int $modelId, int $lguId): void
    {
        DB::table('tenant_lookups')->updateOrInsert(
            ['type' => $type, 'model_id' => $modelId],
            ['lgu_id' => $lguId, 'updated_at' => now()]
        );
    }
}
