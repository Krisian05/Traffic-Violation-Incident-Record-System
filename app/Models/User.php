<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
            ->logOnly(['name', 'username', 'email', 'role'])
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

    public function isAuditor(): bool
    {
        return $this->role === 'auditor';
    }

    /**
     * Whether this user sees every LGU's data instead of being confined to
     * their own assigned lgu_id (super admin + province-wide oversight).
     */
    public function seesAllLgus(): bool
    {
        return $this->isAdmin() || $this->isProvinceAdmin();
    }

    public function lgu(): BelongsTo
    {
        return $this->belongsTo(Lgu::class);
    }

    public function recordedViolations()
    {
        return $this->hasMany(Violation::class, 'recorded_by');
    }
}
