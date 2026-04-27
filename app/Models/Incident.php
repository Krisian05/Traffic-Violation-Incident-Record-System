<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Incident extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['incident_number', 'date_of_incident', 'location', 'status', 'description'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('incident');
    }

    protected $fillable = [
        'incident_number',
        'date_of_incident',
        'time_of_incident',
        'location',
        'description',
        'other_involved',
        'status',
        'recorded_by',
    ];

    protected $casts = [
        'date_of_incident' => 'date',
        'other_involved'   => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            // Use MAX to avoid gaps after deletions and race-condition duplicates
            $isMysql = \DB::getDriverName() === 'mysql';

            if ($isMysql) {
                $maxNum = Incident::withTrashed()
                    ->whereYear('created_at', now()->year)
                    ->whereNotNull('incident_number')
                    ->selectRaw("MAX(CAST(SUBSTRING_INDEX(incident_number, '-', -1) AS UNSIGNED)) as max_num")
                    ->value('max_num') ?? 0;
            } else {
                $maxNum = Incident::withTrashed()
                    ->whereYear('created_at', now()->year)
                    ->whereNotNull('incident_number')
                    ->pluck('incident_number')
                    ->map(fn($n) => (int) last(explode('-', $n)))
                    ->max() ?? 0;
            }

            $model->incident_number = 'INC-' . now()->year . '-' . str_pad($maxNum + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    public function motorists(): HasMany
    {
        return $this->hasMany(IncidentMotorist::class);
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(IncidentMedia::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
