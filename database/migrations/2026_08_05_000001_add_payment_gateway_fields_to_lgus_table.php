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
            $table->string('gateway_provider')->default('paymongo')->after('treasurer_office');
            $table->text('paymongo_public_key')->nullable()->after('gateway_provider');
            $table->text('paymongo_secret_key')->nullable()->after('paymongo_public_key');
            $table->text('paymongo_webhook_secret')->nullable()->after('paymongo_secret_key');
            $table->boolean('enable_manual_qr_claim')->default(true)->after('paymongo_webhook_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lgus', function (Blueprint $table) {
            $table->dropColumn([
                'gateway_provider',
                'paymongo_public_key',
                'paymongo_secret_key',
                'paymongo_webhook_secret',
                'enable_manual_qr_claim',
            ]);
        });
    }
};
