<?php

namespace App\Filament\Exports;

use App\Models\FinancialLog;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class FinancialLogExporter extends Exporter
{
    protected static ?string $model = FinancialLog::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID Catatan')
                ->enabledByDefault(false)
                ->state(function (FinancialLog $record): string {
                    $date = $record->created_at ? $record->created_at->format('Ym') : now()->format('Ym');
                    $paddedId = str_pad($record->id, 4, '0', STR_PAD_LEFT);
                    return "FIN-{$date}-{$paddedId}";
                }),

            ExportColumn::make('date')
                ->label('Tanggal Transaksi')
                ->state(function (FinancialLog $record): string {
                    return $record->date ? \Carbon\Carbon::parse($record->date)->format('Y-m-d') : '-';
                }),

            ExportColumn::make('store.shop_name')
                ->label('Nama Toko')
                ->state(function (FinancialLog $record): string {
                    return $record->store?->shop_name ?? '-';
                }),

            ExportColumn::make('type')
                ->label('Tipe Keuangan')
                ->state(function (FinancialLog $record): string {
                    return match ($record->type) {
                        'income' => 'Pemasukan',
                        'expense' => 'Pengeluaran',
                        default => ucfirst($record->type),
                    };
                }),

            ExportColumn::make('category')
                ->label('Kategori')
                ->state(function (FinancialLog $record): string {
                    return match ($record->category) {
                        'Stock' => 'Pemasukan Barang',
                        'Ads' => 'Iklan',
                        default => $record->category,
                    };
                }),

            ExportColumn::make('payment_method')
                ->label('Metode Pembayaran')
                ->state(function (FinancialLog $record): string {
                    return match ($record->payment_method) {
                        'cash' => 'Cash',
                        'weekly_term' => 'Tempo Mingguan',
                        'monthly_term' => 'Tempo Bulanan',
                        default => ucfirst($record->payment_method),
                    };
                }),

            ExportColumn::make('payment_status')
                ->label('Status Pembayaran')
                ->state(function (FinancialLog $record): string {
                    return match ($record->payment_status) {
                        'paid' => 'Lunas',
                        'unpaid' => 'Belum Lunas',
                        default => ucfirst($record->payment_status),
                    };
                }),

            ExportColumn::make('due_date')
                ->label('Jatuh Tempo')
                ->state(function (FinancialLog $record): string {
                    return $record->due_date ? \Carbon\Carbon::parse($record->due_date)->format('Y-m-d') : '-';
                }),

            // Pastikan amount diekspor sebagai numerik murni tanpa simbol Rp agar Excel tidak bingung
            ExportColumn::make('amount')
                ->label('Nominal (IDR)')
                ->state(function (FinancialLog $record) {
                    return $record->amount;
                }),

            // PENTING: Membersihkan enter (\n atau \r) agar deskripsi panjang tidak merusak baris Excel
            ExportColumn::make('description')
                ->label('Keterangan / Catatan')
                ->state(function (FinancialLog $record): string {
                    if (! $record->description) return '-';

                    // Ganti enter/baris baru menjadi spasi atau penanda koma ( , ) agar tetap satu baris di CSV/Excel
                    $cleanText = str_replace(["\r", "\n"], " ", $record->description);

                    // Opsional: Buang spasi ganda yang berdempetan akibat pembersihan enter tadi
                    return preg_replace('/\s+/', ' ', trim($cleanText));
                }),

            ExportColumn::make('reference_id')
                ->label('ID Referensi')
                ->enabledByDefault(false),

            ExportColumn::make('created_at')
                ->label('Waktu Dibuat')
                ->enabledByDefault(false),
        ];
    }

    public static function getCompletedNotificationTitle(Export $export): string
    {
        return 'Ekspor Catatan Keuangan Selesai';
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $successfulRows = Number::format($export->successful_rows);
        $body = "Ekspor catatan keuangan Anda telah selesai. Sebanyak {$successfulRows} baris data berhasil diekspor.";

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $failedRows = Number::format($failedRowsCount);
            $body .= " Namun, ada {$failedRows} baris data yang gagal diekspor.";
        }

        return $body;
    }
}
