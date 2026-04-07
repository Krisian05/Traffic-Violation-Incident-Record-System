<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Raw SQL — avoids schema builder issues with existing CHECK constraints
        // on PostgreSQL enum columns when using ->change().
        DB::statement('ALTER TABLE vehicles ALTER COLUMN vehicle_type DROP NOT NULL');
    }

    public function down(): void
    {
        // Restore NOT NULL (will fail if any rows have NULL — acceptable for rollback).
        DB::statement('ALTER TABLE vehicles ALTER COLUMN vehicle_type SET NOT NULL');
    }
};
