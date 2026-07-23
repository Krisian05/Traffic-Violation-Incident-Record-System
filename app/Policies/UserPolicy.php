<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isLguAdmin();
    }

    public function view(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isLguAdmin() && ($user->lgu_id === null || $user->lgu_id === $model->lgu_id);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isLguAdmin();
    }

    public function update(User $user, User $model): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isLguAdmin() && ($user->lgu_id === null || $user->lgu_id === $model->lgu_id);
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isLguAdmin() && ($user->lgu_id === null || $user->lgu_id === $model->lgu_id);
    }

    public function restore(User $user, User $model): bool
    {
        return $this->update($user, $model);
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $this->delete($user, $model);
    }
}
