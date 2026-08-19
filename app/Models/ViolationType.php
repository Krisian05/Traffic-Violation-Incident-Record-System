<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ViolationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'lgu_id',
        'name',
        'code',
        'description',
        'fine_amount',
        'fine_amount_2nd',
        'fine_amount_3rd',
        'late_penalty_amount',
        'points',
    ];

    protected $casts = [
        'fine_amount'         => 'decimal:2',
        'fine_amount_2nd'     => 'decimal:2',
        'fine_amount_3rd'     => 'decimal:2',
        'late_penalty_amount' => 'decimal:2',
        'points'              => 'integer',
    ];

    /**
     * Get the designated fine amount for a specific offense attempt (1st, 2nd, 3rd+).
     */
    public function getFineForOffense(int $offense = 1): float
    {
        if ($offense <= 1) {
            return (float) ($this->fine_amount ?? 0);
        }

        if ($offense === 2) {
            return (float) (!is_null($this->fine_amount_2nd) ? $this->fine_amount_2nd : ($this->fine_amount ?? 0));
        }

        // 3rd or subsequent offenses
        if (!is_null($this->fine_amount_3rd)) {
            return (float) $this->fine_amount_3rd;
        }

        if (!is_null($this->fine_amount_2nd)) {
            return (float) $this->fine_amount_2nd;
        }

        return (float) ($this->fine_amount ?? 0);
    }

    /**
     * Whether this violation type defines graduated/escalating fines.
     */
    public function hasTieredFines(): bool
    {
        return !is_null($this->fine_amount_2nd) || !is_null($this->fine_amount_3rd);
    }

    /**
     * Return array of configured offense fine tiers.
     */
    public function getOffenseTiers(): array
    {
        return [
            '1st' => !is_null($this->fine_amount) ? (float)$this->fine_amount : null,
            '2nd' => !is_null($this->fine_amount_2nd) ? (float)$this->fine_amount_2nd : null,
            '3rd' => !is_null($this->fine_amount_3rd) ? (float)$this->fine_amount_3rd : null,
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::saved(function ($model) {
            Cache::forget('violation_types');
            if ($model->lgu_id) {
                Cache::forget("violation_types_lgu_{$model->lgu_id}");
            }
        });
        static::deleted(function ($model) {
            Cache::forget('violation_types');
            if ($model->lgu_id) {
                Cache::forget("violation_types_lgu_{$model->lgu_id}");
            }
        });
    }

    public function lgu()
    {
        return $this->belongsTo(Lgu::class);
    }

    public function scopeForLgu($query, $lguId)
    {
        if (!$lguId) {
            return $query;
        }

        return $query->where('lgu_id', $lguId);
    }

    public function violations()
    {
        return $this->hasMany(Violation::class);
    }
}
