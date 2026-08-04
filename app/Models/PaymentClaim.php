<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'violation_id',
        'payment_method',
        'claimed_reference',
        'claimed_amount',
        'claimant_name',
        'claimant_contact',
        'screenshot_path',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'payment_id',
    ];

    protected $casts = [
        'claimed_amount' => 'decimal:2',
        'reviewed_at'    => 'datetime',
    ];

    public function violation()
    {
        return $this->belongsTo(Violation::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function isPendingReview(): bool
    {
        return $this->status === 'pending_review';
    }
}
