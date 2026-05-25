<?php

namespace App\Filament\Resources\FinancialLogs\Pages;

use App\Filament\Resources\FinancialLogs\FinancialLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFinancialLog extends EditRecord
{
    protected static string $resource = FinancialLogResource::class;

    protected static ?string $title = 'Ubah Pencatatan Keuangan';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading('Hapus Catatan'),
        ];
    }
}
