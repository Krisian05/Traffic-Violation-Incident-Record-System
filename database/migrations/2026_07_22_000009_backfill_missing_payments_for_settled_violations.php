<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Before this release, ViolationController::update() could mark a violation
     * "settled" directly (with an or_number) without ever creating a payments row
     * — the exact bug this release fixes. This catches any such orphaned records
     * so province-wide revenue totals (which now read exclusively from `payments`)
     * don't silently drop real, already-collected money.
     */
    public function up(): void
    {
        $orphans = DB::table('violations')
            ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
            ->leftJoin('payments', 'payments.violation_id', '=', 'violations.id')
            ->where('violations.status', 'settled')
            ->whereNotNull('violations.or_number')
            ->whereNull('payments.id')
            ->select(
                'violations.id as violation_id',
                'violation_types.fine_amount',
                'violations.payment_method',
                'violations.or_number',
                'violations.cashier_name',
                'violations.settled_at',
                'violations.receipt_photo'
            )
            ->get();

        foreach ($orphans as $v) {
            $orNumber = $v->or_number;

            // or_number is unique on payments — if another violation already claimed
            // it, suffix so this settlement still gets recorded (rare data anomaly).
            if (DB::table('payments')->where('or_number', $orNumber)->exists()) {
                $orNumber = $orNumber . '-BACKFILL-' . $v->violation_id;
            }

            DB::table('payments')->insert([
                'violation_id'   => $v->violation_id,
                'amount_paid'    => $v->fine_amount,
                'payment_method' => $v->payment_method ?: 'cash',
                'or_number'      => $orNumber,
                'cashier_name'   => $v->cashier_name ?: 'System (backfilled)',
                'paid_at'        => $v->settled_at ?: now(),
                'receipt_photo'  => $v->receipt_photo,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Not reversible — we can't distinguish backfilled rows from genuine ones
        // once created, beyond the '-BACKFILL-' suffix marker on or_number.
    }
};
