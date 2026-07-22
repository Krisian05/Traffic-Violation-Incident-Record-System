<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lgus', function (Blueprint $table) {
            $table->string('gcash_qr_path')->nullable()->after('treasurer_office');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lgus', function (Blueprint $table) {
            $table->dropColumn('gcash_qr_path');
        });
    }
};
