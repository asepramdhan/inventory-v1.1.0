<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Widgets\CategoryWidget;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Override;

class ListCategories extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CategoryResource::class;

    protected static ?string $title = 'Kelola Kategori';

    #[Override]
    public function getSubheading(): string|Htmlable|null
    {
        return 'Kelola kategori produk';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Buat Kategori'),
        ];
    }

    #[Override]
    protected function getHeaderWidgets(): array
    {
        return [
            CategoryWidget::class,
        ];
    }
}
