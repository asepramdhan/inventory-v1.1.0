<?php

namespace App\Filament\Resources\AdsCosts\Pages;

use App\Filament\Resources\AdsCosts\AdsCostResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAdsCost extends ViewRecord
{
    protected static string $resource = AdsCostResource::class;

    protected static ?string $title = 'Detail Biaya Iklan';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
