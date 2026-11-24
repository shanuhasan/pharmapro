<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyRenewal extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'previous_expires_at' => 'date',
            'new_expires_at' => 'date',
            'amount' => 'decimal:2',
            'reactivated' => 'boolean',
        ];
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    /**
     * The super admin who performed the renewal. Users are tenant-scoped,
     * so drop global scopes to always resolve the name.
     */
    public function renewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'renewed_by')->withoutGlobalScopes();
    }

    public function periodLabel(): string
    {
        if ($this->renew_type === 'months' && $this->months) {
            return $this->months % 12 === 0
                ? ($this->months / 12) . ' ' . str('year')->plural($this->months / 12)
                : $this->months . ' ' . str('month')->plural($this->months);
        }

        return 'Custom date';
    }
}
