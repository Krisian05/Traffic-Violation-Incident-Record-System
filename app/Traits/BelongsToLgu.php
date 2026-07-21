<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToLgu
{
    public static function bootBelongsToLgu(): void
    {
        // Global scope to isolate reads
        static::addGlobalScope('lgu_isolation', function (Builder $builder) {
            if (Auth::check()) {
                /** @var \App\Models\User $user */
                $user = Auth::user();
                // If the user is restricted to an LGU, scope the query to that LGU
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
                if ($user->lgu_id && !$user->isAdmin() && !$user->isProvinceAdmin()) {
                    $model->lgu_id = $user->lgu_id;
                }
            }
        });

        // Event listener to register tenant lookup for routing
        static::saved(function ($model) {
            if (isset($model->lgu_id) && $model->lgu_id) {
                $type = strtolower(class_basename($model));
                if (in_array($type, ['violation', 'incident', 'violator', 'vehicle'])) {
                    \App\Support\Tenant::registerLookup($type, $model->id, $model->lgu_id);
                }
            }
        });

        // Event listener to clean up tenant lookup on delete
        static::deleted(function ($model) {
            $type = strtolower(class_basename($model));
            \Illuminate\Support\Facades\DB::table('tenant_lookups')
                ->where('type', $type)
                ->where('model_id', $model->id)
                ->delete();
        });
    }
}
