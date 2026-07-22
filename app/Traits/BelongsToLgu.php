<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToLgu
{
    public static function bootBelongsToLgu(): void
    {
        // Global scope to strictly isolate reads by user's assigned LGU
        static::addGlobalScope('lgu_isolation', function (Builder $builder) {
            if (Auth::check()) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                // If user is assigned to an LGU and is not super admin / province admin, filter strictly by user's lgu_id
                if ($user->lgu_id && !$user->isAdmin() && !$user->isProvinceAdmin()) {
                    $builder->where($builder->getModel()->getTable() . '.lgu_id', $user->lgu_id);
                }
            }
        });

        // Event listener to auto-tag lgu_id on creation
        static::creating(function ($model) {
            if (empty($model->lgu_id) && Auth::check()) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                if ($user->lgu_id) {
                    $model->lgu_id = $user->lgu_id;
                }
            }
        });
    }
}
