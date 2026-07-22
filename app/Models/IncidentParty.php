<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentParty extends Model
{
    use HasFactory;

    public const ROLES = [
        'passenger'            => 'Passenger',
        'witness'               => 'Witness',
        'reporting_party'       => 'Reporting Party',
        'responding_personnel'  => 'Responding Personnel',
        'other'                 => 'Other',
    ];

    protected $fillable = [
        'incident_id',
        'role',
        'name',
        'contact_number',
        'address',
        'description',
        'responding_user_id',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    /** For responding_personnel entries that are linked to an actual system user/account. */
    public function respondingUser()
    {
        return $this->belongsTo(User::class, 'responding_user_id');
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? ucfirst(str_replace('_', ' ', $this->role));
    }
}
