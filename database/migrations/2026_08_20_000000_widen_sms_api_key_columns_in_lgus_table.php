<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Widen the four API key columns from varchar(255) to text
     * so they can hold Laravel's AES-256-CBC ciphertext (~350 chars).
     */
    public function up(): void
    {
        Schema::table('lgus', function (Blueprint $table) {
            $table->text('sms_api_key')->nullable()->change();
            $table->text('textbee_api_key')->nullable()->change();
            // paymongo_secret_key and paymongo_webhook_secret are already `text` columns
            // from their original migration — no change needed for those.
        });
    }

    public function down(): void
    {
        Schema::table('lgus', function (Blueprint $table) {
            $table->string('sms_api_key', 255)->nullable()->change();
            $table->string('textbee_api_key', 255)->nullable()->change();
        });
    }
};
