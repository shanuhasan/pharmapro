<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
    
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}
