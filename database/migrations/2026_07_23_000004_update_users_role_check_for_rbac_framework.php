<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text IN ('admin', 'super_admin', 'operator', 'lgu_admin', 'traffic_officer', 'issuing_officer', 'province_admin', 'cashier', 'treasurer', 'traffic_supervisor', 'supervisor', 'records_officer', 'auditor', 'view_only'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text IN ('admin', 'operator', 'traffic_officer', 'province_admin', 'cashier', 'treasurer'))");
        }
    }
};
