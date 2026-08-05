<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlinePaymentSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'violation_id',
        'lgu_id',
        'checkout_session_id',
        'payment_intent_id',
        'payment_gateway',
        'amount',
        'currency',
        'payment_method_used',
        'status',
        'checkout_url',
        'gateway_payment_id',
        'payment_id',
        'raw_payload',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'raw_payload' => 'array',
        'paid_at'     => 'datetime',
        'expires_at'  => 'datetime',
    ];

    public function violation()
    {
        return $this->belongsTo(Violation::class);
    }

    public function lgu()
    {
        return $this->belongsTo(Lgu::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
