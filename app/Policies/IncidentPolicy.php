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

    // Editing incidents: Super Admin, or LGU Admin / Records Officer / Traffic Supervisor for own LGU record
    public function update(User $user, Incident $incident): bool
    {
        if ($user->isAuditor() || $user->isCashier() || $user->isTreasurer()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->lgu_id && $incident->lgu_id && (int) $user->lgu_id !== (int) $incident->lgu_id) {
            return false;
        }

        if ($user->isLguAdmin() || $user->isRecordsOfficer() || $user->isTrafficSupervisor()) {
            return true;
        }

        return $user->isIssuingOfficer() && $incident->recorded_by === $user->id;
    }

    // Only owning LGU Admin / Super Admin can delete incidents or their media
    public function delete(User $user, Incident $incident): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->lgu_id && $incident->lgu_id && (int) $user->lgu_id !== (int) $incident->lgu_id) {
            return false;
        }

        return $user->isLguAdmin();
    }

    public function deleteMedia(User $user, Incident $incident): bool
    {
        return $this->delete($user, $incident);
    }

    public function restore(User $user, Incident $incident): bool
    {
        return $this->delete($user, $incident);
    }

    public function forceDelete(User $user, Incident $incident): bool
    {
        return $this->delete($user, $incident);
    }
}
