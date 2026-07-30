<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Violator extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->lgu_id) && Auth::check() && Auth::user()->lgu_id) {
                $model->lgu_id = Auth::user()->lgu_id;
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'middle_name', 'last_name', 'license_number', 'contact_number', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('violator');
    }

    protected $fillable = [
        'lgu_id',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'place_of_birth',
        'gender',
        'civil_status',
        'temporary_address',
        'permanent_address',
        'height',
        'weight',
        'blood_type',
        'valid_id',
        'email',
        'contact_number',
        'license_number',
        'license_type',
        'license_restriction',
        'license_issued_date',
        'license_expiry_date',
        'license_conditions',
        'photo',
    ];

    protected $casts = [
        'date_of_birth'       => 'date',
        'license_issued_date' => 'date',
        'license_expiry_date' => 'date',
    ];

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->first_name, $this->middle_name, $this->last_name]);
        return implode(' ', $parts);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function violations()
    {
        return $this->hasMany(Violation::class);
    }

    public function lgu()
    {
        return $this->belongsTo(Lgu::class);
    }

    public function incidentMotorists()
    {
        return $this->hasMany(IncidentMotorist::class);
    }
}
