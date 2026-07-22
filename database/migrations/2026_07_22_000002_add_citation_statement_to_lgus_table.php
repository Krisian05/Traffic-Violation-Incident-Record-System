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
            $table->text('citation_statement')->nullable()->after('gcash_qr_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lgus', function (Blueprint $table) {
            $table->dropColumn('citation_statement');
        });
    }
};
