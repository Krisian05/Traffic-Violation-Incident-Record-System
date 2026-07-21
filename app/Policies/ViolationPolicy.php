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

    // Only operators can edit violations
    public function update(User $user, Violation $violation): bool
    {
        return $user->isOperator();
    }

    // Only operators can delete violations
    public function delete(User $user, Violation $violation): bool
    {
        return $user->isOperator();
    }

    // Only operators and cashiers can settle violations, scoped by LGU if assigned
    public function settle(User $user, Violation $violation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isOperator()) {
            return !$user->lgu_id || $violation->lgu_id === $user->lgu_id;
        }

        if ($user->isCashier()) {
            return $user->lgu_id && $violation->lgu_id === $user->lgu_id;
        }

        return false;
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
