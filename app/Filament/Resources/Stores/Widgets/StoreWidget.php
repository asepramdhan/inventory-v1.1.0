<?php

namespace App\Filament\Resources\Stores\Widgets;

use App\Filament\Resources\Stores\Pages\ListStores;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StoreWidget extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected function getTablePage(): string
    {
        return ListStores::class;
    }

    protected function getStats(): array
    {
        $userId = Auth::user()->id;

        return [
            Stat::make(
                'Jumlah Toko',
                $this->getPageTableQuery()
                    ->where('user_id', $userId)
                    ->count()
            )
                ->description('Jumlah Semua Toko Aktif & Tidak Aktif'),

            Stat::make(
                'Toko Online',
                $this->getPageTableQuery()
                    ->where('user_id', $userId)
                    ->where('status', 1)
                    ->where('platform', '!=', 'offline')
                    ->count()
            )
                ->description(
                    'Jumlah Toko Offline / Fisik' . ' : ' . $this->getPageTableQuery()
                        ->where('platform', 'offline')
                        ->count()
                )
                ->color('danger'),

            Stat::make(
                'Toko Aktif',
                $this->getPageTableQuery()
                    ->where('user_id', $userId)
                    ->where('status', 1)
                    ->count()
            )
                ->description('Jumlah Semua Toko Aktif')
                ->color('success'),
        ];
    }
}
