<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('violation_id')->constrained('violations')->cascadeOnDelete();
            $table->string('payment_method', 20)->default('gcash');
            $table->string('claimed_reference', 100);
            $table->decimal('claimed_amount', 10, 2);
            $table->string('claimant_name')->nullable();
            $table->string('claimant_contact')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->enum('status', ['pending_review', 'verified', 'rejected'])->default('pending_review');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->timestamps();

            $table->index(['violation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_claims');
    }
};
