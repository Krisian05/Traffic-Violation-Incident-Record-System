<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lgus', function (Blueprint $table) {
            $table->string('maya_qr_path')->nullable()->after('gcash_qr_path');
        });
    }

    public function down(): void
    {
        Schema::table('lgus', function (Blueprint $table) {
            $table->dropColumn('maya_qr_path');
        });
    }
};
