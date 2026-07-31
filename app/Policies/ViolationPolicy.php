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

    // Editing violations: Super Admin, or LGU Admin / Records Officer / Traffic Supervisor for own LGU record; Issuing Officer for own recorded violation
    public function update(User $user, Violation $violation): bool
    {
        if ($user->isAuditor() || $user->isCashier() || $user->isTreasurer()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isIssuingOfficer()) {
            return (int) $violation->recorded_by === (int) $user->id;
        }

        if ($user->lgu_id && $violation->lgu_id && (int) $user->lgu_id !== (int) $violation->lgu_id) {
            return false;
        }

        if ($user->isLguAdmin() || $user->isRecordsOfficer() || $user->isTrafficSupervisor()) {
            return true;
        }

        return (int) $violation->recorded_by === (int) $user->id;
    }

    // Deleting violations: Only owning LGU Admin and Super Admin
    public function delete(User $user, Violation $violation): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->lgu_id && $violation->lgu_id && (int) $user->lgu_id !== (int) $violation->lgu_id) {
            return false;
        }

        return $user->isLguAdmin();
    }

    // Settling violations: Exclusively Cashier and Treasurer for their assigned LGU
    public function settle(User $user, Violation $violation): bool
    {
        if (!$user->isCashier() && !$user->isTreasurer()) {
            return false;
        }

        if ($user->lgu_id && $violation->lgu_id && (int) $user->lgu_id !== (int) $violation->lgu_id) {
            return false;
        }

        return true;
    }

    public function restore(User $user, Violation $violation): bool
    {
        return $this->delete($user, $violation);
    }

    public function forceDelete(User $user, Violation $violation): bool
    {
        return $this->delete($user, $violation);
    }
}
