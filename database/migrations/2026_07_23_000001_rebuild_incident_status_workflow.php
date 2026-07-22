<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Replaces the ad-hoc 4-state status (under_investigation/cleared/solved/settled)
     * with the spec's 6-state workflow: reported, under_assessment,
     * assigned_for_investigation, resolved, closed, referred_to_authority.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE incidents DROP CONSTRAINT IF EXISTS incidents_status_check");
        }

        DB::statement("
            UPDATE incidents SET status = CASE status
                WHEN 'under_investigation' THEN 'assigned_for_investigation'
                WHEN 'cleared'             THEN 'resolved'
                WHEN 'solved'              THEN 'resolved'
                WHEN 'settled'             THEN 'closed'
                ELSE status
            END
        ");

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE incidents ADD CONSTRAINT incidents_status_check CHECK (status IN ('reported', 'under_assessment', 'assigned_for_investigation', 'resolved', 'closed', 'referred_to_authority'))");
            DB::statement("ALTER TABLE incidents ALTER COLUMN status SET DEFAULT 'reported'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE incidents DROP CONSTRAINT IF EXISTS incidents_status_check");
        }

        DB::statement("
            UPDATE incidents SET status = CASE status
                WHEN 'reported'                   THEN 'under_investigation'
                WHEN 'under_assessment'            THEN 'under_investigation'
                WHEN 'assigned_for_investigation'  THEN 'under_investigation'
                WHEN 'resolved'                    THEN 'solved'
                WHEN 'closed'                      THEN 'settled'
                WHEN 'referred_to_authority'        THEN 'settled'
                ELSE status
            END
        ");

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE incidents ADD CONSTRAINT incidents_status_check CHECK (status IN ('under_investigation', 'cleared', 'solved', 'settled'))");
            DB::statement("ALTER TABLE incidents ALTER COLUMN status SET DEFAULT 'under_investigation'");
        }
    }
};
