<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;

#[Guarded(['id'])]
class Category extends Model
{
    // relasi ke product
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
