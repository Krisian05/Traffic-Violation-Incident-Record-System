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

            // Auto-calculate offense attempt count and fine if not provided
            if (!empty($model->violator_id) && !empty($model->violation_type_id)) {
                $attempt = static::calculateOffenseAttempt(
                    (int) $model->violator_id,
                    (int) $model->violation_type_id,
                    $model->id
                );
                if (empty($model->offense_count)) {
                    $model->offense_count = $attempt['attempt_number'];
                }
                if ($model->fine_amount === null || $model->fine_amount === '') {
                    $model->fine_amount = $attempt['fine_amount'];
                }
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['violator_id', 'violation_type_id', 'offense_count', 'fine_amount', 'date_of_violation', 'status', 'location', 'lgu_id', 'ticket_number', 'or_number', 'settled_at'])
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
        'offense_count',
        'fine_amount',
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
        'offense_count'     => 'integer',
        'fine_amount'       => 'decimal:2',
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

    /**
     * Detect offense attempt number (1st, 2nd, 3rd+) and calculate tiered fine.
     */
    public static function calculateOffenseAttempt(int $violatorId, int $violationTypeId, ?int $excludeViolationId = null): array
    {
        $query = static::where('violator_id', $violatorId)
            ->where('violation_type_id', $violationTypeId);

        if ($excludeViolationId) {
            $query->where('id', '!=', $excludeViolationId);
        }

        $priorViolations = $query->orderBy('date_of_violation', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $attemptNumber = $priorViolations->count() + 1;
        $violationType = ViolationType::find($violationTypeId);
        $assessedFine  = $violationType ? $violationType->getFineForOffense($attemptNumber) : 0.0;

        return [
            'attempt_number'   => $attemptNumber,
            'attempt_label'    => static::formatOffenseLabel($attemptNumber),
            'fine_amount'      => round($assessedFine, 2),
            'is_repeat'        => $attemptNumber > 1,
            'prior_count'      => $priorViolations->count(),
            'prior_violations' => $priorViolations->map(fn($v) => [
                'id'                => $v->id,
                'ticket_number'     => $v->ticket_number,
                'date_of_violation' => $v->date_of_violation ? $v->date_of_violation->format('Y-m-d') : null,
                'status'            => $v->status,
                'offense_count'     => $v->offense_count ?? 1,
                'fine_amount'       => (float) (!is_null($v->fine_amount) ? $v->fine_amount : ($v->violationType?->fine_amount ?? 0)),
            ]),
            'has_tiered_fines' => $violationType?->hasTieredFines() ?? false,
            'tiers'            => $violationType?->getOffenseTiers() ?? [],
        ];
    }

    /**
     * Get clean human-readable offense attempt label (e.g. 1st Offense, 2nd Offense).
     */
    public static function formatOffenseLabel(int $attempt): string
    {
        return match ($attempt) {
            1       => '1st Offense',
            2       => '2nd Offense',
            3       => '3rd Offense',
            default => "{$attempt}th Offense",
        };
    }

    /**
     * Offense attempt label for this instance.
     */
    public function offenseLabel(): string
    {
        return static::formatOffenseLabel($this->offense_count ?? 1);
    }

    /**
     * Offense attempt badge styling class.
     */
    public function offenseBadgeClass(): string
    {
        return match ($this->offense_count ?? 1) {
            1       => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
            2       => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            default => 'bg-danger-subtle text-danger border border-danger-subtle',
        };
    }

    /** Additional penalty added once a violation passes its due_date (0 if not overdue or not configured). */
    public function latePenaltyAmount(): float
    {
        if (!$this->isOverdue()) {
            return 0.0;
        }

        return (float) ($this->violationType?->late_penalty_amount ?? 0);
    }

    /** Base fine (using stored fine_amount if present, or calculated tiered fine) plus any late penalty currently owed. */
    public function totalAmountDue(): float
    {
        $base = !is_null($this->fine_amount)
            ? (float) $this->fine_amount
            : (float) ($this->violationType?->getFineForOffense($this->offense_count ?? 1) ?? 0);

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
