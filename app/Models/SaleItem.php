<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
    
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function getHsnCodeAttribute()
    {
        $purchaseItem = \App\Models\PurchaseItem::where('medicine_id', $this->medicine_id)
            ->where('batch_number', $this->batch_number)
            ->latest()
            ->first();
            
        return $purchaseItem && !empty($purchaseItem->hsn_code) 
            ? $purchaseItem->hsn_code 
            : ($this->medicine->hsn_code ?? '-');
    }
}
