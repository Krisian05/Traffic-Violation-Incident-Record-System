<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing data to new status values
        DB::statement("UPDATE incidents SET status = CASE
            WHEN status = 'open'         THEN 'under_investigation'
            WHEN status = 'under_review' THEN 'cleared'
            WHEN status = 'closed'       THEN 'solved'
            ELSE status END");

        // Update column default
        Schema::table('incidents', function (Blueprint $table) {
            $table->string('status')->default('under_investigation')->change();
        });
    }

    public function down(): void
    {
        // Revert data back to old status values
        DB::statement("UPDATE incidents SET status = CASE
            WHEN status = 'under_investigation' THEN 'open'
            WHEN status = 'cleared'             THEN 'under_review'
            WHEN status = 'solved'              THEN 'closed'
            ELSE status END");

        // Restore old default
        Schema::table('incidents', function (Blueprint $table) {
            $table->string('status')->default('open')->change();
        });
    }
};
