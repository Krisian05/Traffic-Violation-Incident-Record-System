<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentStatusHistory extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'incident_id',
        'from_status',
        'to_status',
        'changed_by',
        'note',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
