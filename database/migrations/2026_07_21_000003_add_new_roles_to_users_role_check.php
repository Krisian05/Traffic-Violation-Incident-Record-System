<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The `role` column was left as a native MySQL ENUM('operator','traffic_officer')
        // even after the users_role_check constraint was introduced to allow 'admin' —
        // the ENUM type itself was never widened, so it silently still rejects anything
        // other than those two values. Convert it to a plain string first (same fix
        // already applied to incidents.status in 2026_04_20_000001), then the CHECK
        // constraint below is what actually enforces the allowed values going forward.
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('operator')->change();
        });

        // MySQL 8.4: safely drop then re-add CHECK constraint, expanded to
        // include the new province_admin/cashier/auditor roles.
        $constraint = DB::select("
            SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
            AND CONSTRAINT_NAME = 'users_role_check'
        ");
        if (!empty($constraint)) {
            DB::statement("ALTER TABLE users DROP CHECK users_role_check");
        }
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'operator', 'traffic_officer', 'province_admin', 'cashier', 'auditor'))");
    }

    public function down(): void
    {
        $constraint = DB::select("
            SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
            AND CONSTRAINT_NAME = 'users_role_check'
        ");
        if (!empty($constraint)) {
            DB::statement("ALTER TABLE users DROP CHECK users_role_check");
        }
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'operator', 'traffic_officer'))");
    }
};
