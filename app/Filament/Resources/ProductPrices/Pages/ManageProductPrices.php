<?php

namespace App\Filament\Resources\ProductPrices\Pages;

use App\Filament\Resources\ProductPrices\ProductPriceResource;
use App\Models\ProductPrice;
use App\Models\Store;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Auth;
use Override;

class ManageProductPrices extends ManageRecords
{
    protected static string $resource = ProductPriceResource::class;

    protected static ?string $title = 'Kelola HPP Produk';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah HPP Produk')
                ->modalHeading('Tambah HPP Produk')
                ->modalDescription('Pastikan nama produk belum terdaftar sebelumnya.')
                ->modalWidth('lg')
                ->modalSubmitActionLabel('Tambah')
                ->createAnotherAction(fn(Action $action) => $action->label('Tambah & Buat Lagi'))
                ->icon('heroicon-o-plus-circle')
                ->slideOver(),
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
