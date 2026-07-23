<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'agency')) {
                $table->string('agency')->nullable()->after('role');
            }
            if (!Schema::hasColumn('users', 'badge_id')) {
                $table->string('badge_id')->nullable()->after('agency');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->after('badge_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['agency', 'badge_id', 'status']);
        });
    }
};
