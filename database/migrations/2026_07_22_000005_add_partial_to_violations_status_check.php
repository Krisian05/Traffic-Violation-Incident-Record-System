<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // violations.status was created via $table->enum(...), which on Postgres
        // is implemented as varchar + a named CHECK constraint (violations_status_check).
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE violations DROP CONSTRAINT IF EXISTS violations_status_check");
            DB::statement("ALTER TABLE violations ADD CONSTRAINT violations_status_check CHECK (status IN ('pending', 'partial', 'settled', 'contested'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE violations DROP CONSTRAINT IF EXISTS violations_status_check");
            DB::statement("ALTER TABLE violations ADD CONSTRAINT violations_status_check CHECK (status IN ('pending', 'settled', 'contested'))");
        }
    }
};
