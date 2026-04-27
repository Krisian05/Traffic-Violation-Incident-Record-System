<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Violation extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['violator_id', 'violation_type_id', 'date_of_violation', 'status', 'location', 'ticket_number', 'or_number', 'settled_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('violation');
    }

    protected $fillable = [
        'violator_id',
        'incident_id',
        'vehicle_id',
        'vehicle_owner_name',
        'vehicle_plate',
        'vehicle_make',
        'vehicle_model',
        'vehicle_color',
        'vehicle_or_number',
        'vehicle_cr_number',
        'vehicle_chassis',
        'violation_type_id',
        'date_of_violation',
        'location',
        'ticket_number',
        'citation_ticket_photo',
        'valid_id_photo',
        'status',
        'notes',
        'recorded_by',
        'or_number',
        'cashier_name',
        'receipt_photo',
        'settled_at',
    ];

    protected $casts = [
        'date_of_violation' => 'date',
        'settled_at'        => 'datetime',
    ];

    public function violator()
    {
        return $this->belongsTo(Violator::class);
    }

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function vehiclePhotos()
    {
        return $this->hasMany(ViolationVehiclePhoto::class);
    }

    public function violationType()
    {
        return $this->belongsTo(ViolationType::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /** Pending violations older than 72 hours — countdown starts from date_of_violation */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                     ->where('date_of_violation', '<=', now()->subHours(72)->toDateString());
    }

    /** Pending violations still within the 72-hour window */
    public function scopePendingActive($query)
    {
        return $query->where('status', 'pending')
                     ->where('date_of_violation', '>', now()->subHours(72)->toDateString());
    }

    /** True if this violation instance is overdue */
    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->date_of_violation <= now()->subHours(72);
    }
}
