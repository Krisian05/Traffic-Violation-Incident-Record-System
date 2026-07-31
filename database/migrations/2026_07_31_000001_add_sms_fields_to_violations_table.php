<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->string('sms_status')->default('none')->after('status');
            $table->timestamp('sms_sent_at')->nullable()->after('sms_status');
            $table->text('sms_error')->nullable()->after('sms_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->dropColumn(['sms_status', 'sms_sent_at', 'sms_error']);
        });
    }
};
