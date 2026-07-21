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

    // Only cashiers can settle violations via the Cashier Portal, scoped by LGU.
    // Admins/operators still settle violations directly through the edit form
    // (see update() above) — this ability is exclusive to the dedicated portal.
    public function settle(User $user, Violation $violation): bool
    {
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
