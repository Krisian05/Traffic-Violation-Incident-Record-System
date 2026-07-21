<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Named CHECK constraints are Postgres-only syntax (SQLite, used for local/testing,
        // has no equivalent and doesn't enforce this constraint).
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE incidents DROP CONSTRAINT IF EXISTS incidents_status_check");
            DB::statement("ALTER TABLE incidents ADD CONSTRAINT incidents_status_check CHECK (status IN ('under_investigation', 'cleared', 'solved', 'settled'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE incidents DROP CONSTRAINT IF EXISTS incidents_status_check");
            DB::statement("ALTER TABLE incidents ADD CONSTRAINT incidents_status_check CHECK (status IN ('under_investigation', 'cleared', 'solved'))");
        }
    }
};
