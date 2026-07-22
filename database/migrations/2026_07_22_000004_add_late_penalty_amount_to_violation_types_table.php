<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violation_types', function (Blueprint $table) {
            // Null = no automatic late penalty configured for this violation type.
            $table->decimal('late_penalty_amount', 10, 2)->nullable()->after('fine_amount');
        });
    }

    public function down(): void
    {
        Schema::table('violation_types', function (Blueprint $table) {
            $table->dropColumn('late_penalty_amount');
        });
    }
};
