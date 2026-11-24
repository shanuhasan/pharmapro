<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Pharmacy extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'expires_at' => 'date',
        ];
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(PharmacyRenewal::class)->latest()->latest('id');
    }

    /**
     * The last day of membership. Falls back to 1 year from registration
     * for records created before `expires_at` existed.
     */
    public function membershipEndsAt(): ?Carbon
    {
        if ($this->expires_at) {
            return $this->expires_at->copy()->endOfDay();
        }

        return $this->created_at?->copy()->addYear();
    }

    public function isMembershipExpired(): bool
    {
        $ends = $this->membershipEndsAt();

        return $ends !== null && $ends->isPast();
    }

    /**
     * Whole days left (negative once expired), or null if unknown.
     */
    public function membershipDaysLeft(): ?int
    {
        $ends = $this->membershipEndsAt();

        return $ends ? (int) now()->startOfDay()->diffInDays($ends->copy()->startOfDay(), false) : null;
    }
}
