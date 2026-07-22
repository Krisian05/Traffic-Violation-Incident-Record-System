<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // payment_method on both tables was previously validated only in-app
        // (Laravel `in:` rule) — a raw insert or future bug could store anything.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE violations DROP CONSTRAINT IF EXISTS violations_payment_method_check");
            DB::statement("ALTER TABLE violations ADD CONSTRAINT violations_payment_method_check CHECK (payment_method IS NULL OR payment_method IN ('cash', 'gcash', 'maya', 'bank', 'other'))");

            DB::statement("ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_method_check");
            DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_payment_method_check CHECK (payment_method IN ('cash', 'gcash', 'maya', 'bank', 'other'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE violations DROP CONSTRAINT IF EXISTS violations_payment_method_check");
            DB::statement("ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_payment_method_check");
        }
    }
};
