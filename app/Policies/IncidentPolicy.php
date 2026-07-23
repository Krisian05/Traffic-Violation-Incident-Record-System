<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class IncidentPolicy
{
    // Any authenticated user can list and view incidents
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Incident $incident): bool
    {
        return true;
    }

    // Creating incidents: Super Admin, LGU Admin, Records Officer, Traffic Supervisor, Issuing Officer
    public function create(User $user): bool
    {
        if ($user->isAuditor() || $user->isCashier() || $user->isTreasurer()) {
            return false;
        }

        return true;
    }

    // Editing incidents: Super Admin, LGU Admin, Records Officer, Traffic Supervisor, or Issuing Officer for own record
    public function update(User $user, Incident $incident): bool
    {
        if ($user->isAuditor() || $user->isCashier() || $user->isTreasurer()) {
            return false;
        }

        if ($user->isLguAdmin() || $user->isRecordsOfficer() || $user->isTrafficSupervisor()) {
            return true;
        }

        return $user->isIssuingOfficer() && $incident->recorded_by === $user->id;
    }

    // Only LGU Admin / Super Admin can delete incidents or their media
    public function delete(User $user, Incident $incident): bool
    {
        return $user->isLguAdmin();
    }

    public function deleteMedia(User $user, Incident $incident): bool
    {
        return $user->isLguAdmin();
    }

    public function restore(User $user, Incident $incident): bool
    {
        return $user->isLguAdmin();
    }

    public function forceDelete(User $user, Incident $incident): bool
    {
        return $user->isLguAdmin();
    }
}
