<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Violator;
use Illuminate\Auth\Access\Response;

class ViolatorPolicy
{
    // Any authenticated user can list and view motorists
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Violator $violator): bool
    {
        return true;
    }

    // Both operators and traffic officers can register motorists
    public function create(User $user): bool
    {
        return true;
    }

    // Both operators and traffic officers can edit motorist records
    public function update(User $user, Violator $violator): bool
    {
        return true;
    }

    // Only operators can soft-delete motorists
    public function delete(User $user, Violator $violator): bool
    {
        return $user->isOperator();
    }

    public function restore(User $user, Violator $violator): bool
    {
        return $user->isOperator();
    }

    public function forceDelete(User $user, Violator $violator): bool
    {
        return $user->isOperator();
    }
}
