<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /** Any authenticated user may view payment records. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Payment $payment): bool
    {
        return true;
    }

    /**
     * Voiding a payment is restricted to Treasurer and Super Admin.
     * Must be within the same LGU and the payment must not already be voided.
     */
    public function void(User $user, Payment $payment): bool
    {
        if (!$user->isTreasurer() && !$user->isSuperAdmin()) {
            return false;
        }

        if ($payment->isVoided()) {
            return false;
        }

        // Treasurer can only void payments for their own LGU
        if ($user->lgu_id && $payment->violation?->lgu_id && (int) $user->lgu_id !== (int) $payment->violation->lgu_id) {
            return false;
        }

        return true;
    }

    /**
     * Correcting payment method — same rules as void (Treasurer / Super Admin only).
     */
    public function correct(User $user, Payment $payment): bool
    {
        if (!$user->isTreasurer() && !$user->isSuperAdmin()) {
            return false;
        }

        if ($payment->isVoided()) {
            return false;
        }

        if ($user->lgu_id && $payment->violation?->lgu_id && (int) $user->lgu_id !== (int) $payment->violation->lgu_id) {
            return false;
        }

        return true;
    }
}
