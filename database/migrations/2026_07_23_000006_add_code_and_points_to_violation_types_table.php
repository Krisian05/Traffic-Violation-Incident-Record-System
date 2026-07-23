<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violation_types', function (Blueprint $table) {
            if (!Schema::hasColumn('violation_types', 'code')) {
                $table->string('code')->nullable()->after('name');
            }
            if (!Schema::hasColumn('violation_types', 'points')) {
                $table->integer('points')->default(0)->after('late_penalty_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('violation_types', function (Blueprint $table) {
            $table->dropColumn(['code', 'points']);
        });
    }
};
