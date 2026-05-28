<?php

namespace App\Filament\Resources\AdsCosts\Pages;

use App\Filament\Exports\AdsCostExporter;
use App\Filament\Resources\AdsCosts\AdsCostResource;
use App\Filament\Resources\AdsCosts\Widgets\AdsCostWidget;
use App\Models\AdsCost;
use App\Models\Store;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Auth;
use Override;

class ManageAdsCosts extends ManageRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = AdsCostResource::class;

    protected static ?string $title = 'Kelola Biaya Iklan';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Iklan')
                ->modalHeading('Tambah Iklan Baru')
                ->modalDescription('Pastikan nama iklan belum terdaftar sebelumnya.')
                ->modalWidth('md')
                ->modalSubmitActionLabel('Tambah')
                ->createAnotherAction(fn(Action $action) => $action->label('Tambah & Buat Lagi'))
                // ->preserveFormDataWhenCreatingAnother(fn(array $data) => $data)
                ->icon('heroicon-o-plus-circle')
                ->slideOver(),

            ExportAction::make()
                ->exporter(AdsCostExporter::class)
                ->label('Ekspor'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AdsCostWidget::class,
        ];
    }

    #[Override]
    public function getTabs(): array
    {
        $userId = Auth::id();

        // Ambil semua toko milik user yang sedang login
        $stores = Store::query()->where('user_id', $userId)->get();

        // 1. Tab Utama: Semua Data Iklan
        $tabs = [
            'all' => Tab::make('Semua Toko')
                ->icon('heroicon-o-building-storefront')
                ->badge(static fn(): int => AdsCost::query()->whereHas('store', fn($q) => $q->where('user_id', $userId))->count())
                ->badgeColor('gray'),
        ];

        // 2. Generate Tab Otomatis per Toko yang Anda miliki
        foreach ($stores as $store) {
            $tabs['store_' . $store->id] = Tab::make($store->shop_name)
                ->icon('heroicon-o-presentation-chart-bar')
                ->badge(static fn(): int => AdsCost::query()->where('store_id', $store->id)->count())
                ->badgeColor('warning')
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
