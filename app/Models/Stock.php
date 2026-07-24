<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Stock extends Model
{
    use HasFactory;

    protected $table = 'stock';
    protected $guarded = [];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function scopeExpiringSoon(Builder $query): void
    {
        $query->where('expiry_date', '<=', now()->addDays(30))
              ->where('expiry_date', '>=', now());
    }

    public function scopeLowStock(Builder $query): void
    {
        $query->where('quantity', '<', 10);
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }

    public function getStatusAttribute()
    {
        if ($this->expiry_date) {
            $expiry = \Carbon\Carbon::parse($this->expiry_date);
            if ($expiry->isPast()) {
                return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-800 text-white">Expired</span>';
            }
            if ($expiry->diffInDays(now()) <= 30) {
                return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">Expiring Soon</span>';
            }
        }
        if ($this->quantity < 10) {
            return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Low Stock</span>';
        }
        return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Healthy</span>';
    }
}
