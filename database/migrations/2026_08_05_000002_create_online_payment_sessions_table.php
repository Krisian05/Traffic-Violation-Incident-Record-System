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
        Schema::create('online_payment_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('violation_id')->constrained('violations')->onDelete('cascade');
            $table->foreignId('lgu_id')->nullable()->constrained('lgus')->onDelete('set null');
            $table->string('checkout_session_id')->unique();
            $table->string('payment_intent_id')->nullable()->index();
            $table->string('payment_gateway')->default('paymongo');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('PHP');
            $table->string('payment_method_used')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'expired', 'cancelled'])->default('pending')->index();
            $table->text('checkout_url')->nullable();
            $table->string('gateway_payment_id')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->json('raw_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_payment_sessions');
    }
};
