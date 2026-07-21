<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

use App\Traits\BelongsToLgu;

class Violation extends Model
{
    use HasFactory, SoftDeletes, LogsActivity, BelongsToLgu;

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
        'location',
        'lgu_id',
        'gps_lat',
        'gps_lng',
        'ticket_number',
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
    ];

    protected $casts = [
        'date_of_violation' => 'date',
        'settled_at'        => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            // Auto-generate ticket number only if none was manually entered
            if (!empty($model->ticket_number)) {
                return;
            }

            $year    = now()->year;
            $isMysql = \DB::getDriverName() === 'mysql';

            // LGU code drives the ticket number segment (e.g. TVIRS-CEB-BAL-2026-000001).
            // Falls back to BAL — the current single-pilot LGU — when no LGU could be
            // resolved for this record (e.g. PSGC lookup unavailable at capture time).
            $lguCode = $model->lgu_id ? (Lgu::find($model->lgu_id)?->code) : null;
            $lguCode = $lguCode ?: 'BAL';
            $prefix  = "TVIRS-CEB-{$lguCode}-{$year}-";

            if ($isMysql) {
                $maxNum = Violation::withTrashed()
                    ->whereYear('created_at', $year)
                    ->whereNotNull('ticket_number')
                    ->where('ticket_number', 'like', $prefix . '%')
                    ->selectRaw("MAX(CAST(SUBSTRING_INDEX(ticket_number, '-', -1) AS UNSIGNED)) as max_num")
                    ->value('max_num') ?? 0;
            } else {
                // PostgreSQL
                $maxNum = Violation::withTrashed()
                    ->whereYear('created_at', $year)
                    ->whereNotNull('ticket_number')
                    ->where('ticket_number', 'like', $prefix . '%')
                    ->pluck('ticket_number')
                    ->map(fn($n) => (int) last(explode('-', $n)))
                    ->max() ?? 0;
            }

            $model->ticket_number = $prefix . str_pad($maxNum + 1, 6, '0', STR_PAD_LEFT);
        });
    }

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

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /** Pending violations older than 72 hours — countdown starts from date_of_violation */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                     ->where('date_of_violation', '<=', now()->subHours(72)->toDateString());
    }

    /** Pending violations still within the 72-hour window */
    public function scopePendingActive($query)
    {
        return $query->where('status', 'pending')
                     ->where('date_of_violation', '>', now()->subHours(72)->toDateString());
    }

    /** True if this violation instance is overdue */
    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->date_of_violation <= now()->subHours(72);
    }
}
