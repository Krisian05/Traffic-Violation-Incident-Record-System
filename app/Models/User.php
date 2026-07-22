<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'username', 'email', 'role', 'lgu_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('user');
    }

    protected $fillable = [
        'name',
        'username',
        'email',
        'role',
        'lgu_id',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOperator(): bool
    {
        return in_array($this->role, ['admin', 'operator']);
    }

    public function isTrafficOfficer(): bool
    {
        return $this->role === 'traffic_officer';
    }

    public function isProvinceAdmin(): bool
    {
        return $this->role === 'province_admin';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isTreasurer(): bool
    {
        return $this->role === 'treasurer';
    }

    public function recordedViolations()
    {
        return $this->hasMany(Violation::class, 'recorded_by');
    }

    public function lgu()
    {
        return $this->belongsTo(Lgu::class);
    }

    public function devices()
    {
        return $this->hasMany(DeviceRegistration::class);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_confirmed_at) && ! is_null($this->two_factor_secret);
    }
}
