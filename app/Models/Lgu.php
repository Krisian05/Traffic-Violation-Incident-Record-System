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
        'ordinance_reference',
        'treasurer_office',
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
}
