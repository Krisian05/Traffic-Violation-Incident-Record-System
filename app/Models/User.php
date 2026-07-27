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
        'agency',
        'badge_id',
        'status',
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

    public const ROLE_SUPER_ADMIN = 'admin';
    public const ROLE_PROVINCE_ADMIN = 'province_admin';
    public const ROLE_LGU_ADMIN = 'operator';
    public const ROLE_TREASURER = 'treasurer';
    public const ROLE_CASHIER = 'cashier';
    public const ROLE_TRAFFIC_SUPERVISOR = 'traffic_supervisor';
    public const ROLE_ISSUING_OFFICER = 'traffic_officer';
    public const ROLE_RECORDS_OFFICER = 'records_officer';
    public const ROLE_AUDITOR = 'auditor';

    public const ROLES = [
        'admin'              => 'Super Administrator',
        'super_admin'        => 'Super Administrator',
        'province_admin'     => 'Provincial Administrator',
        'operator'           => 'LGU Administrator',
        'lgu_admin'          => 'LGU Administrator',
        'treasurer'          => 'Treasurer',
        'cashier'            => 'Cashier',
        'traffic_supervisor' => 'Police / Traffic Supervisor',
        'supervisor'         => 'Police / Traffic Supervisor',
        'traffic_officer'    => 'Issuing Officer / Field Personnel',
        'issuing_officer'    => 'Issuing Officer / Field Personnel',
        'records_officer'    => 'Records Officer',
        'auditor'            => 'Auditor / View-Only User',
        'view_only'          => 'Auditor / View-Only User',
    ];

    public const SUPER_ADMIN_ROLES = [
        'admin'          => 'Super Administrator — Full System Administration & Management',
        'province_admin' => 'Provincial Administrator — Province-wide Monitoring & Dashboards',
        'operator'       => 'LGU Administrator — Local System Settings & LGU Management',
    ];

    public const LGU_ADMIN_ROLES = [
        'operator'           => 'LGU Administrator — Local System Settings & LGU Management',
        'treasurer'          => 'Treasurer — Collection Monitoring & Financial Reports',
        'cashier'            => 'Cashier — Payment Validation & Collection Settlement',
        'traffic_supervisor' => 'Police / Traffic Supervisor — Citation Review & Officer Monitoring',
        'traffic_officer'    => 'Issuing Officer / Field Personnel — Citation Ticket & Incident Issuance (Mobile)',
        'records_officer'    => 'Records Officer — Record Management, Encoding & Documentation',
        'auditor'            => 'Auditor / View-Only User — Read-only Access to Reports, Logs & Records',
    ];

    public function assignableRoles(): array
    {
        if ($this->isSuperAdmin()) {
            return self::SUPER_ADMIN_ROLES;
        }

        return self::LGU_ADMIN_ROLES;
    }


    public function isSuperAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isAdmin(): bool
    {
        return $this->isSuperAdmin();
    }

    public function isProvinceAdmin(): bool
    {
        return $this->role === 'province_admin';
    }

    public function isLguAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin', 'operator', 'lgu_admin']);
    }

    public function isOperator(): bool
    {
        return $this->isLguAdmin();
    }

    public function isTreasurer(): bool
    {
        return $this->role === 'treasurer';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isTrafficSupervisor(): bool
    {
        return in_array($this->role, ['traffic_supervisor', 'supervisor']);
    }

    public function isIssuingOfficer(): bool
    {
        return in_array($this->role, ['traffic_officer', 'issuing_officer']);
    }

    public function isTrafficOfficer(): bool
    {
        return $this->isIssuingOfficer();
    }

    public function isRecordsOfficer(): bool
    {
        return $this->role === 'records_officer';
    }

    public function isAuditor(): bool
    {
        return in_array($this->role, ['auditor', 'view_only']);
    }

    public function isReadOnly(): bool
    {
        return $this->isAuditor();
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? ucfirst(str_replace('_', ' ', $this->role));
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
