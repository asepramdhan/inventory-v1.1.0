<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Guarded(['id'])]
class TransactionItem extends Model
{
    // Relasi ke Produk (Penting untuk menarik nama & SKU produk)
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Relasi balik ke Transaksi Utama (Opsional tapi baik untuk keamanan data)
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
