<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;

#[Guarded(['id'])]
class CapitalMutation extends Model
{
    // relasi user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // relasi ke store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
