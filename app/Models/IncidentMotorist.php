<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentMotorist extends Model
{
    protected $fillable = [
        'incident_id',
        'violator_id',
        'motorist_name',
        'motorist_license',
        'motorist_photo',
        'motorist_contact',
        'motorist_address',
        'license_type',
        'license_restriction',
        'license_expiry_date',
        'vehicle_id',
        'vehicle_plate',
        'vehicle_type_manual',
        'vehicle_make',
        'vehicle_model',
        'vehicle_color',
        'vehicle_or_number',
        'vehicle_cr_number',
        'vehicle_chassis',
        'vehicle_photo',
        'vehicle_owner_violator_id',
        'vehicle_owner_name',
        'vehicle_owner_contact',
        'incident_charge_type_id',
        'notes',
    ];

    protected $casts = [
        'license_expiry_date' => 'date',
        'vehicle_photo'       => 'array',
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function violator(): BelongsTo
    {
        return $this->belongsTo(Violator::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function chargeType(): BelongsTo
    {
        return $this->belongsTo(IncidentChargeType::class);
    }

    public function ownerViolator(): BelongsTo
    {
        return $this->belongsTo(Violator::class, 'vehicle_owner_violator_id');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->violator) {
            return $this->violator->full_name ?? '(Unknown)';
        }
        return $this->motorist_name ?? '(Unknown)';
    }

    public function getDisplayLicenseAttribute(): string
    {
        if ($this->violator) {
            return $this->violator->license_number ?? '—';
        }
        return $this->motorist_license ?? '—';
    }

    public function getDisplayPlateAttribute(): string
    {
        if ($this->vehicle) {
            return $this->vehicle->plate_number ?? '—';
        }
        return $this->vehicle_plate ?? '—';
    }

    public function getDisplayVehicleTypeAttribute(): string
    {
        if ($this->vehicle) {
            return $this->vehicle->vehicle_type ?? '—';
        }
        return $this->vehicle_type_manual ?? '—';
    }
}
