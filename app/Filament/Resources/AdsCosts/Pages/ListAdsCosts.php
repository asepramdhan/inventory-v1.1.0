<?php

namespace App\Filament\Resources\AdsCosts\Pages;

use App\Filament\Exports\AdsCostExporter;
use App\Filament\Resources\AdsCosts\AdsCostResource;
use App\Filament\Resources\AdsCosts\Widgets\AdsCostWidget;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListAdsCosts extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = AdsCostResource::class;

    protected static ?string $title = 'Kelola Biaya Iklan';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Biaya Iklan'),
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
}
