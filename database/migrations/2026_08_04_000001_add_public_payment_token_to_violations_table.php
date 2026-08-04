<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->uuid('public_payment_token')->nullable()->unique()->after('ticket_number');
        });

        // Backfill existing rows so every violation (old and new) has a guessable-proof
        // token for the guest payment page — ticket_number is sequential and unsuitable
        // for that purpose (see GuestPaymentController).
        \App\Models\Violation::withTrashed()
            ->whereNull('public_payment_token')
            ->orderBy('id')
            ->chunkById(200, function ($violations) {
                foreach ($violations as $violation) {
                    $violation->update(['public_payment_token' => (string) Str::uuid()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->dropColumn('public_payment_token');
        });
    }
};
