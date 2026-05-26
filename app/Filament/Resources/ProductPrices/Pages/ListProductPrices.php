<?php

namespace App\Filament\Resources\ProductPrices\Pages;

use App\Filament\Resources\ProductPrices\ProductPriceResource;
use App\Models\ProductPrice;
use App\Models\Store;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Override;

class ListProductPrices extends ListRecords
{
    protected static string $resource = ProductPriceResource::class;

    protected static ?string $title = 'Harga Produk';

    #[Override]
    public function getSubheading(): string|Htmlable|null
    {
        return 'Kelola struktur modal dasar (HPP) produk di setiap toko Anda.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat HPP Baru'),
        ];
    }

    #[Override]
    public function getTabs(): array
    {
        $userId = Auth::id();

        // Ambil list semua toko milik user yang login
        $stores = Store::query()->where('user_id', $userId)->get();

        // 1. Tab Utama untuk melihat global data
        $tabs = [
            'all' => Tab::make('Semua Toko')
                ->icon('heroicon-o-squares-2x2')
                ->badge(static fn(): int => ProductPrice::query()->whereHas('store', fn($q) => $q->where('user_id', $userId))->count())
                ->badgeColor('gray')
                ->deferBadge(),
        ];

        // 2. Loop toko untuk jadi sub-tab otomatis
        foreach ($stores as $store) {
            $tabs['store_' . $store->id] = Tab::make($store->shop_name)
                ->icon('heroicon-o-building-storefront')
                ->badge(static fn(): int => ProductPrice::query()->where('store_id', $store->id)->count())
                ->badgeColor('warning')
                ->deferBadge()
                ->modifyQueryUsing(fn($query) => $query->where('store_id', $store->id));
        }

        return $tabs;
    }

    #[Override]
    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}
