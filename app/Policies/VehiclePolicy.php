<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\Response;

class VehiclePolicy
{
    // Any authenticated user can view vehicles (shown on violator profile)
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return true;
    }

    // Both roles can register vehicles
    public function create(User $user): bool
    {
        return $user->isOperator() || $user->isTrafficOfficer();
    }

    // Only operators can edit or delete vehicles
    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->isOperator();
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->isOperator();
    }

    public function restore(User $user, Vehicle $vehicle): bool
    {
        return $user->isOperator();
    }

    public function forceDelete(User $user, Vehicle $vehicle): bool
    {
        return $user->isOperator();
    }
}
