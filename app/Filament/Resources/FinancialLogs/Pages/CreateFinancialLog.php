<?php

namespace App\Filament\Resources\FinancialLogs\Pages;

use App\Filament\Resources\FinancialLogs\FinancialLogResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateFinancialLog extends CreateRecord
{
    protected static string $resource = FinancialLogResource::class;

    protected static ?string $title = 'Buat Catatan Keuangan';

    #[Override]
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Buat Catatan');
    }

    #[Override]
    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Tambah & Buat Lagi');
    }

    #[Override]
    protected function getCreatedNotification(): ?Notification
    {
        return parent::getCreatedNotification()
            ->title('Catatan berhasil dibuat');
    }
}
