<?php

namespace App\Filament\Resources\TemporaryAdsCosts\Pages;

use App\Filament\Resources\TemporaryAdsCosts\TemporaryAdsCostResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTemporaryAdsCosts extends ManageRecords
{
    protected static string $resource = TemporaryAdsCostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
