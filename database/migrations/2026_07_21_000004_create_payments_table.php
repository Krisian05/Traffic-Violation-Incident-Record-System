<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('violation_id')->constrained('violations')->onDelete('cascade');
            $table->decimal('amount_paid', 10, 2);
            $table->string('payment_method', 30);
            $table->string('or_number', 50)->unique();
            $table->string('cashier_name', 150);
            $table->timestamp('paid_at');
            $table->string('receipt_photo')->nullable();
            $table->timestamps();
        });

        // Sync existing settled violations into the new payments table
        $settledViolations = DB::table('violations')
            ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
            ->where('violations.status', 'settled')
            ->whereNotNull('violations.or_number')
            ->select('violations.id as violation_id', 'violation_types.fine_amount', 'violations.payment_method', 'violations.or_number', 'violations.cashier_name', 'violations.settled_at', 'violations.receipt_photo')
            ->get();

        foreach ($settledViolations as $v) {
            // Check if payment already exists (just in case)
            $exists = DB::table('payments')->where('or_number', $v->or_number)->exists();
            if (!$exists) {
                DB::table('payments')->insert([
                    'violation_id'   => $v->violation_id,
                    'amount_paid'    => $v->fine_amount,
                    'payment_method' => $v->payment_method ?: 'cash',
                    'or_number'      => $v->or_number,
                    'cashier_name'   => $v->cashier_name ?: 'System',
                    'paid_at'        => $v->settled_at ?: now(),
                    'receipt_photo'  => $v->receipt_photo,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
