<?php

namespace App\Filament\Resources\Stores\Pages;

use App\Filament\Resources\Stores\StoreResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateStore extends CreateRecord
{
    protected static string $resource = StoreResource::class;

    protected static ?string $title = 'Buat Toko';

    #[Override]
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Buat Toko');
    }

    #[Override]
    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('Tambah & Buat Lagi');
    }

    #[Override]
    protected function getCreatedNotification(): ?Notification
    {
        return parent::getCreatedNotification()->title('Toko berhasil dibuat');
    }
}
