<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Re-encrypt any existing plain-text SMS and PayMongo API keys in the lgus table.
     *
     * The Lgu model now uses Laravel's 'encrypted' cast for:
     *   - sms_api_key
     *   - textbee_api_key
     *   - paymongo_secret_key
     *   - paymongo_webhook_secret
     *
     * This migration reads each value as raw text (bypassing Eloquent casts),
     * checks if it is already a valid Laravel ciphertext, and if not,
     * encrypts it and writes it back. Safe to re-run — already-encrypted
     * values are left untouched.
     */
    public function up(): void
    {
        $columns = [
            'sms_api_key',
            'textbee_api_key',
            'paymongo_secret_key',
            'paymongo_webhook_secret',
        ];

        $lgus = DB::table('lgus')->get();

        foreach ($lgus as $lgu) {
            $updates = [];

            foreach ($columns as $column) {
                $rawValue = $lgu->{$column} ?? null;

                if (empty($rawValue)) {
                    continue; // nothing to encrypt
                }

                // Check if already encrypted by attempting to decrypt.
                // Laravel ciphertext is a base64-encoded JSON string starting
                // with eyJpdiI6 ({"iv":...). If decryption succeeds, skip it.
                if ($this->isAlreadyEncrypted($rawValue)) {
                    continue;
                }

                // Plain-text value found — encrypt it.
                $updates[$column] = Crypt::encryptString($rawValue);

                Log::info("API Key Re-encryption Migration: encrypted {$column} for LGU #{$lgu->id} ({$lgu->name})");
            }

            if (!empty($updates)) {
                DB::table('lgus')->where('id', $lgu->id)->update($updates);
            }
        }
    }

    /**
     * Irreversible — we do not store the original plain-text values.
     * To "undo", the admin would need to re-enter the keys via the LGU settings UI.
     */
    public function down(): void
    {
        // Intentionally a no-op.
        // Decrypting keys back to plain text would reintroduce the vulnerability.
    }

    /**
     * Detect whether a string is already a Laravel-encrypted ciphertext.
     * Laravel's Crypt::encryptString() produces a base64-encoded JSON payload.
     */
    private function isAlreadyEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
};
