<?php

namespace App\Filament\Resources\AdsCosts\Pages;

use App\Filament\Resources\AdsCosts\AdsCostResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAdsCost extends EditRecord
{
    protected static string $resource = AdsCostResource::class;

    protected static ?string $title = 'Ubah Biaya Iklan';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->modalHeading('Hapus Biaya Iklan'),
        ];
    }
}
