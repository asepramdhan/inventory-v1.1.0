<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;

#[Guarded(['id'])]
class FinancialLog extends Model
{
    // relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // relasi ke toko
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // relasi ke transaksi
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
