<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            if (Schema::hasColumn('violations', 'signature_photo')) {
                $table->dropColumn('signature_photo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            if (!Schema::hasColumn('violations', 'signature_photo')) {
                $table->string('signature_photo')->nullable()->after('valid_id_photo');
            }
        });
    }
};
