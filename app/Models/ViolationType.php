<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ViolationType extends Model
{
    use HasFactory;

    protected $fillable = [
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
        static::saved(fn() => Cache::forget('violation_types'));
        static::deleted(fn() => Cache::forget('violation_types'));
    }

    public function violations()
    {
        return $this->hasMany(Violation::class);
    }
}
