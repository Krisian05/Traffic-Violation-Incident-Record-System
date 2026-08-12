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
        'late_penalty_amount',
        'points',
    ];

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
