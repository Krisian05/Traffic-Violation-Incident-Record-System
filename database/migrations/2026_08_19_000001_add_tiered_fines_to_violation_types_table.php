<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violation_types', function (Blueprint $table) {
            $table->decimal('fine_amount_2nd', 10, 2)->nullable()->after('fine_amount');
            $table->decimal('fine_amount_3rd', 10, 2)->nullable()->after('fine_amount_2nd');
        });
    }

    public function down(): void
    {
        Schema::table('violation_types', function (Blueprint $table) {
            $table->dropColumn(['fine_amount_2nd', 'fine_amount_3rd']);
        });
    }
};
