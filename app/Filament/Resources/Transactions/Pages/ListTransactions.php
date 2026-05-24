<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Exports\TransactionExporter;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Resources\Transactions\Widgets\TransactionWidget;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
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
}
