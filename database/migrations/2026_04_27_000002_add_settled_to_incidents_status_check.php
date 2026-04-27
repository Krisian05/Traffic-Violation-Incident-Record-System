<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE incidents DROP CONSTRAINT IF EXISTS incidents_status_check");
        DB::statement("ALTER TABLE incidents ADD CONSTRAINT incidents_status_check CHECK (status IN ('under_investigation', 'cleared', 'solved', 'settled'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE incidents DROP CONSTRAINT IF EXISTS incidents_status_check");
        DB::statement("ALTER TABLE incidents ADD CONSTRAINT incidents_status_check CHECK (status IN ('under_investigation', 'cleared', 'solved'))");
    }
};
