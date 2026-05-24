<?php

namespace App\Filament\Resources\Categories\Widgets;

use App\Filament\Resources\Categories\Pages\ListCategories;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CategoryWidget extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected function getTablePage(): string
    {
        return ListCategories::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Jumlah Kategori', $this->getPageTableQuery()->count()),
            Stat::make('Kategori Aktif', $this->getPageTableQuery()->where('status', 1)->count()),
            Stat::make('Kategori Tidak Aktif', $this->getPageTableQuery()->where('status', 0)->count()),
        ];
    }
}
