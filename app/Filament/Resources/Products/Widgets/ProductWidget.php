<?php

namespace App\Filament\Resources\Products\Widgets;

use App\Filament\Resources\Products\Pages\ListProducts;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ProductWidget extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    protected static bool $isLazy = true;

    protected function getTablePage(): string
    {
        return ListProducts::class;
    }

    protected function getStats(): array
    {
        $userId = Auth::user()->id;

        return [
            Stat::make(
                'Jumlah Produk',
                $this->getPageTableQuery()
                    ->where('user_id', $userId)
                    ->count()
            ),
            Stat::make(
                'Produk Aktif',
                $this->getPageTableQuery()
                    ->where('user_id', $userId)
                    ->where('status', 1)
                    ->count()
            ),
            Stat::make(
                'Produk Tidak Aktif',
                $this->getPageTableQuery()
                    ->where('user_id', $userId)
                    ->where('status', 0)
                    ->count()
            ),
        ];
    }
}
