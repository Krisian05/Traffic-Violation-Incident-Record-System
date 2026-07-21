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
            ->logOnly(['violation_id', 'amount_paid', 'payment_method', 'or_number', 'cashier_name', 'paid_at'])
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
        'paid_at',
        'receipt_photo',
    ];

    protected $casts = [
        'paid_at'     => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    public function violation()
    {
        return $this->belongsTo(Violation::class);
    }
}
