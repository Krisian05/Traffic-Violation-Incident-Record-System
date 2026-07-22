<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentStatusHistory;
use App\Models\User;
use InvalidArgumentException;

/**
 * Single write path for changing an incident's status, so every transition
 * (from the desktop operator UI, the mobile officer UI, or the raw API) is
 * captured in incident_status_histories — a purpose-built audit trail,
 * separate from the generic activity log.
 */
class IncidentStatusService
{
    public function changeStatus(Incident $incident, string $toStatus, User $user, ?string $note = null): Incident
    {
        if (!array_key_exists($toStatus, Incident::STATUSES)) {
            throw new InvalidArgumentException("Invalid incident status: {$toStatus}");
        }

        $fromStatus = $incident->status;

        if ($fromStatus === $toStatus) {
            return $incident;
        }

        $incident->update(['status' => $toStatus]);

        IncidentStatusHistory::create([
            'incident_id' => $incident->id,
            'from_status' => $fromStatus,
            'to_status'   => $toStatus,
            'changed_by'  => $user->id,
            'note'        => $note,
        ]);

        return $incident;
    }

    /** Record the initial status on a freshly created incident (from_status = null). */
    public function recordInitialStatus(Incident $incident, User $user): void
    {
        IncidentStatusHistory::create([
            'incident_id' => $incident->id,
            'from_status' => null,
            'to_status'   => $incident->status,
            'changed_by'  => $user->id,
            'note'        => null,
        ]);
    }
}
