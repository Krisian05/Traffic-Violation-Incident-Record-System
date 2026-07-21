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
            // Drop the check constraint that limits role to operator/traffic_officer,
            // then add a new one that also allows 'admin'.
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'operator', 'traffic_officer'))");
        }

        DB::table('users')
            ->where('username', 'admin')
            ->where('role', 'operator')
            ->update(['role' => 'admin']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('username', 'admin')
            ->where('role', 'admin')
            ->update(['role' => 'operator']);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('operator', 'traffic_officer'))");
        }
    }
};
