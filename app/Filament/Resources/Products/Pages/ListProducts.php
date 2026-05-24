<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Products\Widgets\ProductWidget;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Override;

class ListProducts extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Master Produk';

    #[Override]
    public function getSubheading(): string|Htmlable|null
    {
        return 'Kelola inventori master produk';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Buat Produk'),
        ];
    }

    #[Override]
    protected function getHeaderWidgets(): array
    {
        return [
            ProductWidget::class,
        ];
    }
}
