<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lgus', function (Blueprint $table) {
            $table->string('sms_api_key')->nullable()->after('maya_qr_path');
            $table->string('sms_sender_name')->default('TVIRS')->after('sms_api_key');
            $table->boolean('sms_auto_send')->default(true)->after('sms_sender_name');
        });
    }

    public function down(): void
    {
        Schema::table('lgus', function (Blueprint $table) {
            $table->dropColumn(['sms_api_key', 'sms_sender_name', 'sms_auto_send']);
        });
    }
};
