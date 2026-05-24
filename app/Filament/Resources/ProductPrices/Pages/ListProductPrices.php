<?php

namespace App\Filament\Resources\ProductPrices\Pages;

use App\Filament\Resources\ProductPrices\ProductPriceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Override;

class ListProductPrices extends ListRecords
{
    protected static string $resource = ProductPriceResource::class;

    protected static ?string $title = 'Harga Produk';

    #[Override]
    public function getSubheading(): string|Htmlable|null
    {
        return 'Kelola harga atau HPP produk';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat HPP Baru'),
        ];
    }
}
