<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;

#[Guarded(['id'])]
class Store extends Model
{
    // relasi ke transaction
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // relasi ke ProductPrice
    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }
}
