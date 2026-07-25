<?php

namespace App\Traits;

use App\Models\Scopes\PharmacyScope;
use Illuminate\Support\Facades\Auth;
use App\Models\Pharmacy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasPharmacy
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new PharmacyScope);

        static::creating(function ($model) {
            if (Auth::hasUser() && Auth::user()->role !== 'super_admin' && !$model->pharmacy_id) {
                $model->pharmacy_id = Auth::user()->pharmacy_id;
            }
        });
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }
}
