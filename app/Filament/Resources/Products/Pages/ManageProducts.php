<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Auth;
use Override;

class ManageProducts extends ManageRecords
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Kelola Produk';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Produk')
                ->modalHeading('Tambah Produk Baru')
                ->modalDescription('Pastikan nama produk belum terdaftar sebelumnya.')
                ->modalWidth('lg')
                ->modalSubmitActionLabel('Tambah Produk')
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
            // Tab 1: Semua Koleksi Produk
            'all' => Tab::make('Semua Produk')
                ->icon('heroicon-o-squares-2x2')
                ->badge(static fn(): int => Product::query()->where('user_id', $userId)->count())
                ->badgeColor('gray')
                ->deferBadge(),

            // Tab 2: Produk yang Sedang Aktif Dijual
            'active' => Tab::make('Aktif')
                ->icon('heroicon-o-check-circle')
                ->badge(static fn(): int => Product::query()->where('user_id', $userId)->where('status', true)->count())
                ->badgeColor('success')
                ->deferBadge()
                ->modifyQueryUsing(fn($query) => $query->where('status', true)),

            // Tab 3: Produk yang Sedang Dinonaktifkan (Gudang/Arsip)
            'inactive' => Tab::make('Nonaktif')
                ->icon('heroicon-o-archive-box')
                ->badge(static fn(): int => Product::query()->where('user_id', $userId)->where('status', false)->count())
                ->badgeColor('danger')
                ->deferBadge()
                ->modifyQueryUsing(fn($query) => $query->where('status', false)),
        ];
    }

    #[Override]
    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }
}
