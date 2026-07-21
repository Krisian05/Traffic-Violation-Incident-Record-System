<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tokens were previously stored in plaintext. Hash the existing rows in place
     * (same SHA-256 algorithm the app now uses for lookups) so already-issued
     * officer sessions keep working after the app switches to hashed comparison.
     */
    public function up(): void
    {
        DB::table('api_tokens')->orderBy('id')->chunkById(200, function ($tokens) {
            foreach ($tokens as $token) {
                // Already a 64-char hex hash (re-running this migration, or a token
                // issued after the app switched to hashing) — leave it alone.
                if (preg_match('/^[a-f0-9]{64}$/', $token->token)) {
                    continue;
                }

                DB::table('api_tokens')
                    ->where('id', $token->id)
                    ->update(['token' => hash('sha256', $token->token)]);
            }
        });
    }

    public function down(): void
    {
        // Irreversible: the plaintext tokens are not recoverable from their hash.
    }
};
