<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Override;

class ListCategories extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CategoryResource::class;

    protected static ?string $title = 'Kelola Kategori';

    #[Override]
    public function getSubheading(): string|Htmlable|null
    {
        return 'Kelola kategori produk Anda untuk mempermudah pengelompokan inventori.';
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
            // CategoryWidget::class,
        ];
    }

    #[Override]
    public function getTabs(): array
    {
        $userId = Auth::id();

        return [
            // Tab 1: Semua Data Kategori
            'all' => Tab::make('Semua Kategori')
                ->icon('heroicon-o-rectangle-group')
                ->badge(static fn(): int => Category::query()->where('user_id', $userId)->count())
                ->badgeColor('gray')
                ->deferBadge(),

            // Tab 2: Kategori yang Sedang Digunakan (Aktif)
            'active' => Tab::make('Aktif')
                ->icon('heroicon-o-check-circle')
                ->badge(static fn(): int => Category::query()->where('user_id', $userId)->where('status', true)->count())
                ->badgeColor('success')
                ->deferBadge()
                ->modifyQueryUsing(fn($query) => $query->where('status', true)),

            // Tab 3: Kategori yang Sedang Diarsipkan (Nonaktif)
            'inactive' => Tab::make('Nonaktif')
                ->icon('heroicon-o-archive-box')
                ->badge(static fn(): int => Category::query()->where('user_id', $userId)->where('status', false)->count())
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
