<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;

#[Guarded(['id'])]
class AdsCost extends Model
{
    // relasi ke store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
