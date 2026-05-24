<?php

namespace App\Filament\Resources\Stores\Pages;

use App\Filament\Resources\Stores\StoreResource;
use App\Filament\Resources\Stores\Widgets\StoreWidget;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Override;

class ListStores extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = StoreResource::class;

    protected static ?string $title = 'Pengaturan Toko';

    #[Override]
    public function getSubheading(): string|Htmlable|null
    {
        return 'Kelola toko penjualan online dan offline kamu.';
    }

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Buat Toko'),
        ];
    }

    #[Override]
    protected function getHeaderWidgets(): array
    {
        return [
            StoreWidget::class,
        ];
    }
}
