<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Violation extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    // #19 — Consolidated single booted() method (was split across booted() and boot())
    protected static function booted(): void
    {
        static::creating(function ($model) {
            // Auto-assign LGU from authenticated user
            if (empty($model->lgu_id) && Auth::check() && Auth::user()->lgu_id) {
                $model->lgu_id = Auth::user()->lgu_id;
            }

            // Auto-generate due_date from the grace period unless explicitly set
            if (empty($model->due_date) && !empty($model->date_of_violation)) {
                $graceDays = (int) config('tvirs.payment.grace_period_days', 3);
                $model->due_date = \Carbon\Carbon::parse($model->date_of_violation)->addDays($graceDays)->toDateString();
            }

            // Auto-generate ticket number only if none was manually entered
            if (empty($model->ticket_number)) {
                $model->ticket_number = static::generateTicketNumber($model->lgu_id);
            }

            // Non-guessable token for the public guest-payment page (ticket_number is
            // sequential and must never be used to grant access to a citation's details).
            if (empty($model->public_payment_token)) {
                $model->public_payment_token = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['violator_id', 'violation_type_id', 'date_of_violation', 'status', 'location', 'lgu_id', 'ticket_number', 'or_number', 'settled_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('violation');
    }

    protected $fillable = [
        'violator_id',
        'incident_id',
        'vehicle_id',
        'vehicle_owner_name',
        'vehicle_plate',
        'vehicle_make',
        'vehicle_model',
        'vehicle_color',
        'vehicle_or_number',
        'vehicle_cr_number',
        'vehicle_chassis',
        'violation_type_id',
        'date_of_violation',
        'due_date',
        'location',
        'lgu_id',
        'gps_lat',
        'gps_lng',
        'ticket_number',
        'public_payment_token',
        'citation_ticket_photo',
        'valid_id_photo',
        'status',
        'notes',
        'recorded_by',
        'or_number',
        'cashier_name',
        'payment_method',
        'receipt_photo',
        'settled_at',
        'sms_status',
        'sms_sent_at',
        'sms_reminder_sent_at',
        'sms_error',
    ];

    protected $casts = [
        'date_of_violation' => 'date',
        'due_date'          => 'date',
        'settled_at'        => 'datetime',
        'sms_sent_at'       => 'datetime',
        'sms_reminder_sent_at' => 'datetime',
    ];

    public static function generateTicketNumber(?int $lguId = null): string
    {
        $year    = now()->year;
        $isMysql = \DB::getDriverName() === 'mysql';

        $lguCode = $lguId ? (Lgu::find($lguId)?->code) : null;
        $lguCode = $lguCode ?: 'BAL';
        $prefix  = "TVIRS-CEB-{$lguCode}-{$year}-";

        if ($isMysql) {
            $maxNum = static::withTrashed()
                ->whereYear('created_at', $year)
                ->whereNotNull('ticket_number')
                ->where('ticket_number', 'like', $prefix . '%')
                ->selectRaw("MAX(CAST(SUBSTRING_INDEX(ticket_number, '-', -1) AS UNSIGNED)) as max_num")
                ->value('max_num') ?? 0;
        } else {
            $maxNum = static::withTrashed()
                ->whereYear('created_at', $year)
                ->whereNotNull('ticket_number')
                ->where('ticket_number', 'like', $prefix . '%')
                ->pluck('ticket_number')
                ->map(fn($n) => (int) last(explode('-', $n)))
                ->max() ?? 0;
        }

        return $prefix . str_pad($maxNum + 1, 6, '0', STR_PAD_LEFT);
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function violator()
    {
        return $this->belongsTo(Violator::class);
    }

    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function vehiclePhotos()
    {
        return $this->hasMany(ViolationVehiclePhoto::class);
    }

    public function violationType()
    {
        return $this->belongsTo(ViolationType::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function lgu()
    {
        return $this->belongsTo(Lgu::class);
    }

    /** All payments (including voided). */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /** Only active (non-voided) payments. */
    public function activePayments()
    {
        return $this->hasMany(Payment::class)->active();
    }

    /** Most recent active payment recorded for this violation, if any. */
    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->active()->latestOfMany('paid_at');
    }

    /** Guest-submitted online GCash payment claims awaiting staff verification. */
    public function paymentClaims()
    {
        return $this->hasMany(PaymentClaim::class);
    }

    /** The most recent claim still awaiting staff review, if any. */
    public function pendingPaymentClaim()
    {
        return $this->hasOne(PaymentClaim::class)->where('status', 'pending_review')->latestOfMany();
    }

    /** Online checkout sessions created via payment gateways (e.g. PayMongo). */
    public function onlinePaymentSessions()
    {
        return $this->hasMany(OnlinePaymentSession::class);
    }

    /** Pending online checkout session, if any. */
    public function activeOnlineSession()
    {
        return $this->hasOne(OnlinePaymentSession::class)->where('status', 'pending')->latestOfMany();
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /** Unpaid violations whose due_date has passed. */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                     ->whereNotNull('due_date')
                     ->where('due_date', '<', now()->toDateString());
    }

    /** Unpaid violations still within their due date. */
    public function scopePendingActive($query)
    {
        return $query->where('status', 'pending')
                     ->where(function ($q) {
                         $q->whereNull('due_date')->orWhere('due_date', '>=', now()->toDateString());
                     });
    }

    // ── Financial Helpers ───────────────────────────────────────────────────

    /** True if this violation instance is past its due date and still unpaid. */
    public function isOverdue(): bool
    {
        if ($this->status !== 'pending' || empty($this->due_date)) {
            return false;
        }

        $dueDate = $this->due_date instanceof \Carbon\CarbonInterface
            ? $this->due_date
            : \Carbon\Carbon::parse($this->due_date);

        return now()->startOfDay()->gt($dueDate->startOfDay());
    }

    /** Additional penalty added once a violation passes its due_date (0 if not overdue or not configured). */
    public function latePenaltyAmount(): float
    {
        if (!$this->isOverdue()) {
            return 0.0;
        }

        return (float) ($this->violationType?->late_penalty_amount ?? 0);
    }

    /** Base fine plus any late penalty currently owed. */
    public function totalAmountDue(): float
    {
        $base = (float) ($this->violationType?->fine_amount ?? 0);

        return round($base + $this->latePenaltyAmount(), 2);
    }

    /**
     * Sum of all active (non-voided) payments recorded against this violation.
     * Uses eager-loaded total_paid attribute from withSum when available (#17 N+1 fix).
     */
    public function totalAmountPaid(): float
    {
        // If withSum('activePayments as total_paid', ...) was used, use cached value
        if (isset($this->attributes['total_paid'])) {
            return (float) ($this->attributes['total_paid'] ?? 0);
        }

        return (float) $this->activePayments()->sum('amount_paid');
    }

    /** Remaining balance owed (never negative). */
    public function balanceRemaining(): float
    {
        return max(0.0, round($this->totalAmountDue() - $this->totalAmountPaid(), 2));
    }

    /** Determine if violation is fully settled. */
    public function isSettled(): bool
    {
        return $this->status === 'settled';
    }

    /** Generate a unique suggested OR number for payment settlement. */
    public function suggestOrNumber(): string
    {
        if (!empty($this->or_number)) {
            return $this->or_number;
        }

        $year    = $this->date_of_violation ? $this->date_of_violation->year : now()->year;
        $lguCode = $this->lgu?->code ?: ($this->violator?->lgu?->code ?: 'CEB');

        $baseSuffix = (string) $this->id;
        if ($this->ticket_number && str_contains($this->ticket_number, '-')) {
            $parts      = explode('-', $this->ticket_number);
            $baseSuffix = end($parts);
        }

        $candidate = "OR-{$lguCode}-{$year}-" . str_pad($baseSuffix, 6, '0', STR_PAD_LEFT);

        $counter = 1;
        $finalOr = $candidate;
        while (Payment::where('or_number', $finalOr)->exists()) {
            $finalOr = "OR-{$lguCode}-{$year}-" . str_pad($baseSuffix . "-{$counter}", 6, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $finalOr;
    }
}
