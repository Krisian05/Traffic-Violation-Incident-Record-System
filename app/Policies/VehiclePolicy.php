<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\Response;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Vehicle $vehicle): bool
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

    public function update(User $user, Vehicle $vehicle): bool
    {
        if ($user->isAuditor() || $user->isCashier() || $user->isTreasurer()) {
            return false;
        }

        return $user->isLguAdmin() || $user->isRecordsOfficer() || $user->isTrafficSupervisor() || $user->isIssuingOfficer();
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->isLguAdmin();
    }

    public function restore(User $user, Vehicle $vehicle): bool
    {
        return $user->isLguAdmin();
    }

    public function forceDelete(User $user, Vehicle $vehicle): bool
    {
        return $user->isLguAdmin();
    }
}
