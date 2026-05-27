<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
// use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Override;

class ManageCategories extends ManageRecords
{
    protected static string $resource = CategoryResource::class;

    protected static ?string $title = 'Kelola Kategori';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Kategori Baru')
                ->modalHeading('Tambah Kategori Produk') // Judul di atas modal
                ->modalDescription('Pastikan nama kategori belum terdaftar sebelumnya.') // Deskripsi kecil di bawah judul
                ->modalWidth('md') // Mengatur lebar modal (ExtraSmall, Small, Medium, Large, sampai 7Xl / Full)
                ->modalSubmitActionLabel('Buat Kategori') // Mengubah teks tombol "Create"
                ->createAnotherAction(fn(Action $action) => $action->label('Tambah & Buat Lagi'))
                ->icon('heroicon-o-plus-circle') // Menambahkan ikon di tombol pemicu
                ->slideOver(),
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
