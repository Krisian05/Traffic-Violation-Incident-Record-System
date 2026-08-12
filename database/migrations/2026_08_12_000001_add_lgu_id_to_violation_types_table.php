<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violation_types', function (Blueprint $table) {
            if (!Schema::hasColumn('violation_types', 'lgu_id')) {
                $table->foreignId('lgu_id')->nullable()->after('id')->constrained('lgus')->nullOnDelete();
            }
        });

        // Drop legacy unique constraint on name if it exists
        try {
            Schema::table('violation_types', function (Blueprint $table) {
                $table->dropUnique('violation_types_name_unique');
            });
        } catch (\Throwable $e) {}

        // Add composite index for lgu_id & name query performance
        Schema::table('violation_types', function (Blueprint $table) {
            $table->index(['lgu_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('violation_types', function (Blueprint $table) {
            if (Schema::hasColumn('violation_types', 'lgu_id')) {
                $table->dropForeign(['lgu_id']);
                $table->dropColumn('lgu_id');
            }
        });
    }
};
