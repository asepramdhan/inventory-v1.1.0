<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;

#[Guarded(['id'])]
class Product extends Model
{
    // relasi ke category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

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
