<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old check constraint first so the UPDATE is allowed
        DB::statement("ALTER TABLE incidents DROP CONSTRAINT IF EXISTS incidents_status_check");

        // Migrate existing data to new status values
        DB::statement("UPDATE incidents SET status = CASE
            WHEN status = 'open'         THEN 'under_investigation'
            WHEN status = 'under_review' THEN 'cleared'
            WHEN status = 'closed'       THEN 'solved'
            ELSE status END");

        // Add new check constraint with updated allowed values
        DB::statement("ALTER TABLE incidents ADD CONSTRAINT incidents_status_check CHECK (status IN ('under_investigation', 'cleared', 'solved'))");

        // Update column default
        DB::statement("ALTER TABLE incidents ALTER COLUMN status SET DEFAULT 'under_investigation'");
    }

    public function down(): void
    {
        // Drop new check constraint
        DB::statement("ALTER TABLE incidents DROP CONSTRAINT IF EXISTS incidents_status_check");

        // Revert data back to old status values
        DB::statement("UPDATE incidents SET status = CASE
            WHEN status = 'under_investigation' THEN 'open'
            WHEN status = 'cleared'             THEN 'under_review'
            WHEN status = 'solved'              THEN 'closed'
            ELSE status END");

        // Restore old check constraint
        DB::statement("ALTER TABLE incidents ADD CONSTRAINT incidents_status_check CHECK (status IN ('open', 'under_review', 'closed'))");

        // Restore old default
        DB::statement("ALTER TABLE incidents ALTER COLUMN status SET DEFAULT 'open'");
    }
};
