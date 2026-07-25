<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class PharmacyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::hasUser()) {
            $user = Auth::user();
            // Super admins can see everything by default, unless explicitly scoped
            if ($user->role !== 'super_admin') {
                $builder->where($model->getTable() . '.pharmacy_id', $user->pharmacy_id);
            }
        }
    }
}
