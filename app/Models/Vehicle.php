<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'violator_id',
        'plate_number',
        'vehicle_type',
        'make',
        'model',
        'color',
        'year',
        'or_number',
        'cr_number',
        'chassis_number',
        'owner_name',
    ];

    public function violator()
    {
        return $this->belongsTo(Violator::class);
    }

    public function violations()
    {
        return $this->hasMany(Violation::class);
    }

    public function photos()
    {
        return $this->hasMany(VehiclePhoto::class);
    }

    /** First violation vehicle photo — used as thumbnail fallback when no direct vehicle photo exists */
    public function firstViolationPhoto()
    {
        return $this->hasOneThrough(
            ViolationVehiclePhoto::class,
            Violation::class,
            'vehicle_id',   // FK on violations → vehicles
            'violation_id', // FK on violation_vehicle_photos → violations
            'id',
            'id'
        );
    }

    public function incidentMotorists()
    {
        return $this->hasMany(IncidentMotorist::class);
    }
}
