<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Exports\TransactionExporter;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Resources\Transactions\Widgets\TransactionWidget;
use App\Models\Transaction;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Override;

class ListTransactions extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = TransactionResource::class;

    protected static ?string $title = 'Transaksi Penjualan';

    #[Override]
    public function getSubheading(): string|Htmlable|null
    {
        return 'Catat penjualan online dan offline harian kamu.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Transaksi Baru'),
            ExportAction::make()
                ->exporter(TransactionExporter::class)
                ->label('Ekspor'),
        ];
    }

    #[Override]
    protected function getHeaderWidgets(): array
    {
        return [
            TransactionWidget::class,
        ];
    }

    #[Override]
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->icon('heroicon-o-bars-4')
                ->badge(static fn(): int => Transaction::query()->where('user_id', Auth::user()->id)->count())
                ->badgeColor('primary')
                ->deferBadge(),
            'diproses' => Tab::make('Diproses')
                ->icon('heroicon-o-clock')
                ->badge(static fn(): int => Transaction::query()->where('user_id', Auth::user()->id)->where('status', 'Diproses')->count())
                ->badgeColor('warning')
                ->deferBadge()
                ->modifyQueryUsing(fn($query) => $query->where('status', 'Diproses')),
            'dikirim' => Tab::make('Dikirim')
                ->icon('heroicon-o-truck')
                ->badge(static fn(): int => Transaction::query()->where('user_id', Auth::user()->id)->where('status', 'Dikirim')->count())
                ->badgeColor('success')
                ->deferBadge()
                ->modifyQueryUsing(fn($query) => $query->where('status', 'Dikirim')),
            'selesai' => Tab::make('Selesai')
                ->icon('heroicon-o-check-circle')
                ->badge(static fn(): int => Transaction::query()->where('user_id', Auth::user()->id)->where('status', 'Selesai')->count())
                ->badgeColor('success')
                ->deferBadge()
                ->modifyQueryUsing(fn($query) => $query->where('status', 'Selesai')),
            'dibatalkan' => Tab::make('Dibatalkan')
                ->icon('heroicon-o-x-circle')
                ->badge(static fn(): int => Transaction::query()->where('user_id', Auth::user()->id)->where('status', 'Dibatalkan')->count())
                ->badgeColor('danger')
                ->deferBadge()
                ->modifyQueryUsing(fn($query) => $query->where('status', 'Dibatalkan')),
        ];
    }

    #[Override]
    public function getDefaultActiveTab(): string|int|null
    {
        return 'diproses';
    }
}
