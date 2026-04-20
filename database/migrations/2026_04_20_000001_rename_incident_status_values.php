<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing data first
        DB::statement("UPDATE incidents SET status = CASE
            WHEN status = 'open'         THEN 'under_investigation'
            WHEN status = 'under_review' THEN 'cleared'
            WHEN status = 'closed'       THEN 'solved'
            ELSE status END");

        // Change the enum and default
        DB::statement("ALTER TABLE incidents MODIFY COLUMN status ENUM('cleared','under_investigation','solved') NOT NULL DEFAULT 'under_investigation'");
    }

    public function down(): void
    {
        // Revert enum and default first
        DB::statement("ALTER TABLE incidents MODIFY COLUMN status ENUM('open','under_review','closed','cleared','under_investigation','solved') NOT NULL DEFAULT 'open'");

        DB::statement("UPDATE incidents SET status = CASE
            WHEN status = 'under_investigation' THEN 'open'
            WHEN status = 'cleared'             THEN 'under_review'
            WHEN status = 'solved'              THEN 'closed'
            ELSE status END");

        DB::statement("ALTER TABLE incidents MODIFY COLUMN status ENUM('open','under_review','closed') NOT NULL DEFAULT 'open'");
    }
};
