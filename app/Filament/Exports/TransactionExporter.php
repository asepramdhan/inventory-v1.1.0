<?php

namespace App\Filament\Exports;

use App\Models\Transaction;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class TransactionExporter extends Exporter
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID Transaksi')
                ->enabledByDefault(false)
                ->state(function (Transaction $record): string {
                    // Hasilnya: TRX - TAHUN BULAN - ID (Format angka 4 digit, misal: 0012)
                    $date = $record->created_at->format('Ym');
                    $paddedId = str_pad($record->id, 4, '0', STR_PAD_LEFT);

                    return "TRX-{$date}-{$paddedId}";
                }),

            ExportColumn::make('order_number')
                ->label('Nomor Pesanan'),

            ExportColumn::make('store.shop_name')
                ->label('Nama Toko'),

            // 3. Menggabungkan daftar produk dari tabel transaction_items
            ExportColumn::make('items')
                ->label('Daftar Produk (Qty)')
                ->state(function (Transaction $record): string {
                    return $record->items->map(function ($item) {
                        // Hasilnya seperti: "Sepatu Nike (2x)"
                        return "{$item->product->name} ({$item->quantity}x)";
                    })->implode(', ');
                }),

            ExportColumn::make('total_price')
                ->label('Total Harga'),

            ExportColumn::make('status')
                ->label('Status'),

            ExportColumn::make('notes')
                ->label('Catatan')
                ->enabledByDefault(false),

            ExportColumn::make('created_at')
                ->label('Tanggal Transaksi'),
        ];
    }

    public static function getCompletedNotificationTitle(Export $export): string
    {
        return 'Ekspor Selesai';
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        // Memformat angka dengan standar ribuan (misal: 1.000)
        $successfulRows = Number::format($export->successful_rows);

        $body = "Ekspor transaksi Anda telah selesai. Sebanyak {$successfulRows} baris data berhasil diekspor.";

        // Memeriksa jika ada baris data yang gagal diekspor
        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $failedRows = Number::format($failedRowsCount);
            $body .= " Namun, ada {$failedRows} baris data yang gagal diekspor.";
        }

        return $body;
    }
}
