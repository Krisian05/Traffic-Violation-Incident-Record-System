<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing data to new status values
        DB::statement("UPDATE incidents SET status = CASE
            WHEN status = 'open'         THEN 'under_investigation'
            WHEN status = 'under_review' THEN 'cleared'
            WHEN status = 'closed'       THEN 'solved'
            ELSE status END");

        // Named CHECK constraints and ALTER COLUMN ... SET DEFAULT are Postgres-only syntax
        // (SQLite, used for local/testing, has no equivalent and doesn't enforce this constraint).
        if (DB::getDriverName() === 'pgsql') {
            // Drop the old check constraint first so the UPDATE above was allowed
            DB::statement("ALTER TABLE incidents DROP CONSTRAINT IF EXISTS incidents_status_check");

            // Add new check constraint with updated allowed values
            DB::statement("ALTER TABLE incidents ADD CONSTRAINT incidents_status_check CHECK (status IN ('under_investigation', 'cleared', 'solved'))");

            // Update column default
            DB::statement("ALTER TABLE incidents ALTER COLUMN status SET DEFAULT 'under_investigation'");
        }
    }

    public function down(): void
    {
        // Revert data back to old status values
        DB::statement("UPDATE incidents SET status = CASE
            WHEN status = 'under_investigation' THEN 'open'
            WHEN status = 'cleared'             THEN 'under_review'
            WHEN status = 'solved'              THEN 'closed'
            ELSE status END");

        if (DB::getDriverName() === 'pgsql') {
            // Drop new check constraint
            DB::statement("ALTER TABLE incidents DROP CONSTRAINT IF EXISTS incidents_status_check");

            // Restore old check constraint
            DB::statement("ALTER TABLE incidents ADD CONSTRAINT incidents_status_check CHECK (status IN ('open', 'under_review', 'closed'))");

            // Restore old default
            DB::statement("ALTER TABLE incidents ALTER COLUMN status SET DEFAULT 'open'");
        }
    }
};
