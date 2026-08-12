<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Single write path for recording payments against a violation.
 *
 * Both the Cashier Portal and the operator settlement action funnel through
 * recordPayment() so a violation can never end up "settled" without a
 * matching row in the payments table, and partial payments stay consistent
 * with the violation's status.
 */
class PaymentService
{
    /**
     * @param array{or_number:string,cashier_name:string,payment_method:string,amount_paid?:float|null,paid_at?:\DateTimeInterface|null,receipt_photo?:string|null} $data
     */
    public function recordPayment(Violation $violation, array $data, ?User $collector = null): Payment
    {
        return DB::transaction(function () use ($violation, $data, $collector) {
            $violation = Violation::whereKey($violation->id)->lockForUpdate()->firstOrFail();
            $violation->loadMissing('violationType');

            $balance = $violation->balanceRemaining();
            $amount  = isset($data['amount_paid']) && $data['amount_paid'] !== null && $data['amount_paid'] !== ''
                ? (float) $data['amount_paid']
                : $balance;

            // #4 — Use bccomp for precise financial comparisons instead of float tolerance
            if (bccomp((string) $balance, '0', 2) <= 0) {
                throw new InvalidArgumentException('This violation has no outstanding balance.');
            }
            if (bccomp((string) $amount, '0', 2) <= 0) {
                throw new InvalidArgumentException('Amount paid must be greater than zero.');
            }
            if (bccomp((string) $amount, (string) $balance, 2) > 0) {
                throw new InvalidArgumentException('Amount paid (₱' . number_format($amount, 2) . ') cannot exceed the outstanding balance of ₱' . number_format($balance, 2) . '.');
            }

            $payment = Payment::create([
                'violation_id'   => $violation->id,
                'amount_paid'    => round($amount, 2),
                'payment_method' => $data['payment_method'],
                'or_number'      => $data['or_number'],
                'cashier_name'   => $data['cashier_name'],
                'collected_by'   => $collector?->id,
                'paid_at'        => $data['paid_at'] ?? now(),
                'receipt_photo'  => $data['receipt_photo'] ?? null,
            ]);

            $this->syncViolationStatus($violation, $payment);

            app(NotificationService::class)->notifyPaymentSettled($violation, $payment);

            return $payment;
        });
    }

    /**
     * Settle a violation automatically when confirmed via PayMongo webhook.
     */
    public function settleFromOnlineSession(\App\Models\OnlinePaymentSession $session, string $gatewayPaymentId, ?string $paymentMethod = 'gcash', ?array $rawWebhookPayload = null): Payment
    {
        return DB::transaction(function () use ($session, $gatewayPaymentId, $paymentMethod, $rawWebhookPayload) {
            $session = \App\Models\OnlinePaymentSession::whereKey($session->id)->lockForUpdate()->firstOrFail();

            if ($session->isPaid() && $session->payment_id) {
                return Payment::findOrFail($session->payment_id);
            }

            $violation = Violation::whereKey($session->violation_id)->lockForUpdate()->firstOrFail();

            $orNumber = $violation->suggestOrNumber();
            $cashierName = 'PayMongo Gateway (' . strtoupper($paymentMethod ?: 'GCASH') . ' — Ref ' . $gatewayPaymentId . ')';

            $payment = $this->recordPayment($violation, [
                'or_number'      => $orNumber,
                'cashier_name'   => $cashierName,
                'payment_method' => strtolower($paymentMethod ?: 'gcash'),
                'amount_paid'    => $session->amount,
                'paid_at'        => now(),
            ], null);

            $session->update([
                'status'              => 'paid',
                'gateway_payment_id'  => $gatewayPaymentId,
                'payment_method_used' => $paymentMethod,
                'payment_id'          => $payment->id,
                'paid_at'             => now(),
                'raw_payload'         => $rawWebhookPayload ?: $session->raw_payload,
            ]);

            app(SmsService::class)->sendPaymentConfirmationSms($violation, $payment);

            return $payment;
        });
    }

    /**
     * Void a payment — marks it as voided and recomputes the violation status.
     * Only Treasurer or Super Admin should call this (enforced by PaymentPolicy).
     */
    public function voidPayment(Payment $payment, User $actor, string $reason): void
    {
        DB::transaction(function () use ($payment, $actor, $reason) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->isVoided()) {
                throw new InvalidArgumentException('This payment has already been voided.');
            }

            $payment->update([
                'voided_at'   => now(),
                'voided_by'   => $actor->id,
                'void_reason' => $reason,
            ]);

            $violation = Violation::whereKey($payment->violation_id)->lockForUpdate()->firstOrFail();
            $this->syncViolationStatus($violation);
        });
    }

    /** Recompute status/settlement fields from the actual active payments recorded so far. */
    public function syncViolationStatus(Violation $violation, ?Payment $latestPayment = null): void
    {
        $violation->refresh();
        $violation->loadMissing('violationType');

        // Only count active (non-voided) payments
        $totalPaid = (float) $violation->activePayments()->sum('amount_paid');
        $totalDue  = $violation->totalAmountDue();
        $latest    = $latestPayment && !$latestPayment->isVoided()
            ? $latestPayment
            : $violation->activePayments()->latest('paid_at')->first();

        if ($totalPaid <= 0) {
            // All payments voided or none recorded — revert to pending
            $violation->update([
                'status'         => 'pending',
                'settled_at'     => null,
                'or_number'      => null,
                'cashier_name'   => null,
                'payment_method' => null,
            ]);
            return;
        }

        // Financial comparison: full payment marks violation as settled, otherwise pending
        $status = bccomp((string) $totalPaid, (string) $totalDue, 2) >= 0 ? 'settled' : 'pending';

        $updates = ['status' => $status];
        $updates['settled_at'] = $status === 'settled' ? ($violation->settled_at ?? now()) : null;

        if ($latest) {
            $updates['or_number']      = $latest->or_number;
            $updates['cashier_name']   = $latest->cashier_name;
            $updates['payment_method'] = $latest->payment_method;
            if ($latest->receipt_photo) {
                $updates['receipt_photo'] = $latest->receipt_photo;
            }
        }

        $violation->update($updates);
    }
}
