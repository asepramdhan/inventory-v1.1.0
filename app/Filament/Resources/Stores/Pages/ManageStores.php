<?php

namespace App\Filament\Resources\Stores\Pages;

use App\Filament\Resources\Stores\StoreResource;
use App\Models\Store;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Auth;
use Override;

class ManageStores extends ManageRecords
{
    protected static string $resource = StoreResource::class;

    protected static ?string $title = 'Kelola Toko';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Toko Baru')
                ->modalHeading('Tambah Toko')
                ->modalDescription('Pastikan nama toko belum terdaftar sebelumnya.')
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

        return [
            // 1. Semua Platform
            'all' => Tab::make('Semua Platform')
                ->icon('heroicon-o-building-storefront')
                ->badge(static fn(): int => Store::query()->where('user_id', $userId)->count())
                ->badgeColor('gray')
                ->deferBadge(),

            // 2. Shopee
            'shopee' => Tab::make('Shopee')
                ->icon('heroicon-o-shopping-bag')
                ->badge(static fn(): int => Store::query()->where('user_id', $userId)->where('platform', 'shopee')->count())
                ->badgeColor('primary')
                ->deferBadge()
                ->modifyQueryUsing(fn($query) => $query->where('platform', 'shopee')),

            // 3. Lazada (Tambahan Baru)
            'lazada' => Tab::make('Lazada')
                ->icon('heroicon-o-shopping-cart')
                ->badge(static fn(): int => Store::query()->where('user_id', $userId)->where('platform', 'lazada')->count())
                ->badgeColor('primary')
                ->deferBadge()
                ->modifyQueryUsing(fn($query) => $query->where('platform', 'lazada')),

            // 4. TikTok Shop
            'tiktok' => Tab::make('TikTok Shop')
                ->icon('heroicon-o-video-camera')
                ->badge(static fn(): int => Store::query()->where('user_id', $userId)->where('platform', 'tiktok')->count())
                ->badgeColor('primary')
                ->deferBadge()
                ->modifyQueryUsing(fn($query) => $query->where('platform', 'tiktok')),

            // 5. Toko Offline / Manual
            'offline' => Tab::make('Offline / Manual')
                ->icon('heroicon-o-home')
                ->badge(static fn(): int => Store::query()->where('user_id', $userId)->where('platform', 'offline')->count())
                ->badgeColor('gray')
                ->deferBadge()
                ->modifyQueryUsing(fn($query) => $query->where('platform', 'offline')),
        ];
    }

    #[Override]
    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}
