<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lgu extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'province',
        'psgc_city_code',
        'ordinance_reference',
        'treasurer_office',
        'gcash_qr_path',
        'maya_qr_path',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    /** Resolve an LGU from the PSGC city/municipality code captured by the location selector. */
    public static function findByPsgcCityCode(?string $code): ?self
    {
        if (empty($code)) {
            return null;
        }

        return static::where('psgc_city_code', $code)->first();
    }
}
