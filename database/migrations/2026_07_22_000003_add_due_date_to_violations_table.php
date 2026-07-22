<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('date_of_violation');
        });

        // Backfill existing rows using the grace period that replaces the
        // previously hardcoded 72-hour overdue window (~3 days).
        $graceDays = (int) config('tvirs.payment.grace_period_days', 3);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("UPDATE violations SET due_date = (date_of_violation + INTERVAL '{$graceDays} days')::date WHERE due_date IS NULL");
        } else {
            DB::statement("UPDATE violations SET due_date = date(date_of_violation, '+{$graceDays} days') WHERE due_date IS NULL");
        }
    }

    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->dropColumn('due_date');
        });
    }
};
