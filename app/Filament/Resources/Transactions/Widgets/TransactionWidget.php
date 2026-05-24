<?php

namespace App\Filament\Resources\Transactions\Widgets;

use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Models\TransactionItem;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransactionWidget extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected function getTablePage(): string
    {
        return ListTransactions::class;
    }

    protected function getStats(): array
    {
        // 1. Ambil query yang sudah terfilter oleh tabel (Toko, Periode, dll)
        $filteredQuery = $this->getPageTableQuery();

        // 2. Ambil data Omset dari query yang terfilter
        $omsetFiltered = (clone $filteredQuery)->sum('total_price');

        // 3. Hitung baris item (SKU) dari query yang terfilter
        $skuCount = TransactionItem::whereIn(
            'transaction_id',
            (clone $filteredQuery)->pluck('id')
        )->count();

        // 4. Hitung total fisik barang dari query yang terfilter
        $itemsSold = (clone $filteredQuery)
            ->withSum('items as total_qty', 'quantity')
            ->get()
            ->sum('total_qty');

        // 5. Data Chart (Tetap 7 hari terakhir, abaikan filter tabel agar chart tetap informatif)
        // Gunakan model langsung agar tidak terpengaruh filter tanggal tabel
        $chartData = collect(range(6, 0))->map(
            fn($days) => \App\Models\Transaction::whereDate('created_at', now()->subDays($days))->sum('total_price')
        )->toArray();

        return [
            Stat::make('Total Omset', 'Rp ' . number_format($omsetFiltered, 0, ',', '.'))
                ->description('Berdasarkan filter saat ini')
                ->chart($chartData)
                ->color('success'),

            Stat::make('Produk Terjual (SKU)', "{$skuCount} Item")
                ->description('Total baris produk terjual')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make('Total Fisik Barang', "{$itemsSold} Pcs")
                ->description('Total kuantitas barang keluar')
                ->descriptionIcon('heroicon-m-cube')
                ->color('warning'),
        ];
    }
}
