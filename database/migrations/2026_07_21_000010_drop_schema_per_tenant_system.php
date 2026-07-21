<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the schema-per-LGU multi-tenancy system in favor of a single
 * schema with an lgu_id column on each table. The schema-per-tenant clones
 * shared a Postgres id sequence with their public-schema originals (a
 * consequence of `CREATE TABLE ... LIKE ... INCLUDING ALL` not creating
 * independent sequences for legacy SERIAL columns), so the same numeric id
 * could silently refer to two different records depending on which schema's
 * search_path was active — this caused real data to fork during edits.
 * Any data that only existed in lgu_bal has already been reconciled back
 * into the public tables before this migration runs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('tenant_lookups');

        if (DB::getDriverName() === 'pgsql') {
            $schemas = DB::select("SELECT schema_name FROM information_schema.schemata WHERE schema_name LIKE 'lgu_%'");
            foreach ($schemas as $schema) {
                DB::statement("DROP SCHEMA IF EXISTS {$schema->schema_name} CASCADE");
            }
        }
    }

    public function down(): void
    {
        // Not reversible — the schema-per-tenant system is being retired, not paused.
    }
};
