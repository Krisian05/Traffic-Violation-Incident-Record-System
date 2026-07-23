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

    // Creating violations: Super Admin, LGU Admin, Records Officer, Traffic Supervisor, Issuing Officer
    public function create(User $user): bool
    {
        if ($user->isAuditor() || $user->isCashier() || $user->isTreasurer()) {
            return false;
        }

        return true;
    }

    // Editing violations: Super Admin, LGU Admin, Records Officer, Traffic Supervisor, or Issuing Officer for own record
    public function update(User $user, Violation $violation): bool
    {
        if ($user->isAuditor() || $user->isCashier() || $user->isTreasurer()) {
            return false;
        }

        if ($user->isLguAdmin() || $user->isRecordsOfficer() || $user->isTrafficSupervisor()) {
            return true;
        }

        return $user->isIssuingOfficer() && $violation->recorded_by === $user->id;
    }

    // Deleting violations: Only LGU Admin and Super Admin
    public function delete(User $user, Violation $violation): bool
    {
        return $user->isLguAdmin();
    }

    // Settling violations: LGU Admin / Super Admin, or Cashier / Treasurer for their LGU
    public function settle(User $user, Violation $violation): bool
    {
        if ($user->isLguAdmin()) {
            return true;
        }

        return ($user->isCashier() || $user->isTreasurer()) && $user->lgu_id && $violation->lgu_id === $user->lgu_id;
    }

    public function restore(User $user, Violation $violation): bool
    {
        return $user->isLguAdmin();
    }

    public function forceDelete(User $user, Violation $violation): bool
    {
        return $user->isLguAdmin();
    }
}
