<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Violator;
use Illuminate\Auth\Access\Response;

class ViolatorPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Violator $violator): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        if ($user->isAuditor() || $user->isCashier() || $user->isTreasurer()) {
            return false;
        }

        return true;
    }

    public function update(User $user, Violator $violator): bool
    {
        if ($user->isAuditor() || $user->isCashier() || $user->isTreasurer()) {
            return false;
        }

        return $user->isLguAdmin() || $user->isRecordsOfficer() || $user->isTrafficSupervisor() || $user->isIssuingOfficer();
    }

    public function delete(User $user, Violator $violator): bool
    {
        return $user->isLguAdmin();
    }

    public function restore(User $user, Violator $violator): bool
    {
        return $user->isLguAdmin();
    }

    public function forceDelete(User $user, Violator $violator): bool
    {
        return $user->isLguAdmin();
    }
}
