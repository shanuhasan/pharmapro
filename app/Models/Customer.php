<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, \App\Traits\HasPharmacy;

    protected $guarded = [];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}

