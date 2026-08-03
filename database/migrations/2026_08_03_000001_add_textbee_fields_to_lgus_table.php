<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lgus', function (Blueprint $table) {
            $table->string('sms_provider')->default('textbee')->after('maya_qr_path');
            $table->string('textbee_api_key')->nullable()->after('sms_provider');
            $table->string('textbee_device_id')->nullable()->after('textbee_api_key');
        });
    }

    public function down(): void
    {
        Schema::table('lgus', function (Blueprint $table) {
            $table->dropColumn(['sms_provider', 'textbee_api_key', 'textbee_device_id']);
        });
    }
};
