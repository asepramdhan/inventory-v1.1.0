<?php

namespace App\Filament\Imports;

use App\Models\Transaction;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class TransactionImporter extends Importer
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            // Memetakan kolom 'order_number' dari CSV/Excel ke database
            ImportColumn::make('order_number')
                ->label('Nomor Pesanan')
                ->rules(['required', 'string']),

            // Memetakan kolom 'status' yang ingin diperbarui
            ImportColumn::make('status')
                ->label('Status Transaksi')
                ->rules(['required', 'string']),
        ];
    }

    public function resolveRecord(): ?Transaction
    {
        // STRATEGI UTAMA: Cari baris transaksi yang sudah ada berdasarkan order_number
        // Jika ketemu, statusnya akan langsung ditimpa (di-update).
        // Jika tidak ketemu, kita return null agar tidak membuat data kosong baru.

        $record = Transaction::where('order_number', $this->data['order_number'])->first();

        if (! $record) {
            return null; // Mengabaikan baris jika nomor pesanan tidak terdaftar di sistem
        }

        return $record;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Proses import data transaksi telah selesai. ' . number_format($import->successful_rows) . ' baris berhasil diperbarui.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' baris gagal diproses (Nomor pesanan mungkin tidak ditemukan).';
        }

        return $body;
    }
}
