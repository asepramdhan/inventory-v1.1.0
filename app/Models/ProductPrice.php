<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;

#[Guarded(['id'])]
class ProductPrice extends Model
{
    // relasi ke product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // relasi ke store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
