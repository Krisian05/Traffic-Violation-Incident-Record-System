<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL 8.4: safely drop then re-add CHECK constraint
        // MySQL supports CHECK constraints but not "DROP CONSTRAINT IF EXISTS"
        $constraint = DB::select("
            SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'incidents'
            AND CONSTRAINT_NAME = 'incidents_status_check'
        ");
        if (!empty($constraint)) {
            DB::statement("ALTER TABLE incidents DROP CHECK incidents_status_check");
        }
        DB::statement("ALTER TABLE incidents ADD CONSTRAINT incidents_status_check CHECK (status IN ('under_investigation', 'cleared', 'solved', 'settled'))");
    }

    public function down(): void
    {
        $constraint = DB::select("
            SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'incidents'
            AND CONSTRAINT_NAME = 'incidents_status_check'
        ");
        if (!empty($constraint)) {
            DB::statement("ALTER TABLE incidents DROP CHECK incidents_status_check");
        }
        DB::statement("ALTER TABLE incidents ADD CONSTRAINT incidents_status_check CHECK (status IN ('under_investigation', 'cleared', 'solved'))");
    }
};
