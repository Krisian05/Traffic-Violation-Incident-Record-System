<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Violation;
use Illuminate\Auth\Access\Response;

class ViolationPolicy
{
    // Any authenticated user can list and view violations
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Violation $violation): bool
    {
        return true;
    }

    // Both roles can record violations
    public function create(User $user): bool
    {
        return true;
    }

    // Operators can edit any violation; traffic officers can edit only the ones they recorded.
    public function update(User $user, Violation $violation): bool
    {
        return $user->isOperator()
            || ($user->isTrafficOfficer() && $violation->recorded_by === $user->id);
    }

    // Only operators can delete violations
    public function delete(User $user, Violation $violation): bool
    {
        return $user->isOperator();
    }

    // Operators/admins can settle any violation (e.g. LGUs without a dedicated
    // cashier account). Cashiers may only settle tickets for their own LGU via
    // the Cashier Portal. This is now the ONLY path that creates a Payment row
    // — the edit form (update() above) can no longer set status directly.
    public function settle(User $user, Violation $violation): bool
    {
        if ($user->isOperator()) {
            return true;
        }

        return $user->isCashier() && $user->lgu_id && $violation->lgu_id === $user->lgu_id;
    }

    public function restore(User $user, Violation $violation): bool
    {
        return $user->isOperator();
    }

    public function forceDelete(User $user, Violation $violation): bool
    {
        return $user->isOperator();
    }
}
