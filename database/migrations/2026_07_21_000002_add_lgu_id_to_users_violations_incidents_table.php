<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('lgu_id')->nullable()->after('role')->constrained('lgus')->nullOnDelete();
        });

        Schema::table('violations', function (Blueprint $table) {
            $table->foreignId('lgu_id')->nullable()->after('location')->constrained('lgus')->nullOnDelete();
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->foreignId('lgu_id')->nullable()->after('location')->constrained('lgus')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lgu_id');
        });

        Schema::table('violations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lgu_id');
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lgu_id');
        });
    }
};
