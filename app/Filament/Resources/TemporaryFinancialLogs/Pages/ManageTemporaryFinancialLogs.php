<?php

namespace App\Filament\Resources\TemporaryFinancialLogs\Pages;

use App\Filament\Resources\TemporaryFinancialLogs\TemporaryFinancialLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTemporaryFinancialLogs extends ManageRecords
{
    protected static string $resource = TemporaryFinancialLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
