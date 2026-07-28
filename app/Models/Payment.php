<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['violation_id', 'amount_paid', 'payment_method', 'or_number', 'cashier_name', 'collected_by', 'paid_at', 'voided_at', 'voided_by', 'void_reason'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('payment');
    }

    protected $fillable = [
        'violation_id',
        'amount_paid',
        'payment_method',
        'or_number',
        'cashier_name',
        'collected_by',
        'paid_at',
        'receipt_photo',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'paid_at'     => 'datetime',
        'voided_at'   => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function violation()
    {
        return $this->belongsTo(Violation::class);
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function voidedByUser()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Exclude voided payments from queries. */
    public function scopeActive($query)
    {
        return $query->whereNull('voided_at');
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }
}
