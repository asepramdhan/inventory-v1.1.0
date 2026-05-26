<?php

namespace App\Filament\Exports;

use App\Models\CapitalMutation;
use Carbon\Carbon;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class CapitalMutationExporter extends Exporter
{
    protected static ?string $model = CapitalMutation::class;

    public static function getColumns(): array
    {
        return [
            // 1. Tanggal Kejadian (Paling kiri sebagai acuan utama)
            ExportColumn::make('created_at')
                ->label('Tanggal')
                ->formatStateUsing(fn($state): string => Carbon::parse($state)->format('d-m-Y H:i')),

            // 2. Identitas Channel/Toko
            // ExportColumn::make('store.shop_name')
            //     ->label('Toko')
            //     ->default('Global / Non-Toko'), // <--- Menangani data yang store_id-nya null

            // 3. Jenis Transaksi
            ExportColumn::make('type')
                ->label('Jenis Mutasi')
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    'withdrawal' => 'Penarikan Saldo',
                    'supplier_payment' => 'Pembayaran Produsen',
                    default => ucfirst($state),
                }),

            // 4. Arus Rekening (Dari -> Ke)
            ExportColumn::make('source')
                ->label('Dari'),

            ExportColumn::make('destination')
                ->label('Ke'),

            // 5. Nominal Uang
            ExportColumn::make('amount')
                ->label('Nominal')
                ->formatStateUsing(fn(float $state): string => Number::format($state, locale: 'id')),

            // 6. Status Khusus untuk Pembayaran Produsen (Tempo)
            ExportColumn::make('payment_status')
                ->label('Status')
                ->formatStateUsing(fn(?string $state): string => match ($state) {
                    'paid' => 'Lunas',
                    'unpaid' => 'Tempo (Belum Bayar)',
                    default => $state ?? '-',
                }),

            // ExportColumn::make('due_date')
            //     ->label('Jatuh Tempo'),

            // 7. Data Pelengkap di paling kanan
            ExportColumn::make('reference_number')
                ->label('No. Reff'),

            ExportColumn::make('description')
                ->label('Keterangan'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor data mutasi keuangan Anda telah selesai. Sebanyak ' . Number::format($export->successful_rows) . ' baris data berhasil diunduh.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' Terjadi kegagalan pada ' . Number::format($failedRowsCount) . ' baris data.';
        }

        return $body;
    }
}
