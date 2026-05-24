<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Override;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected static ?string $title = 'Buat Kategori';

    #[Override]
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Buat Kategori');
    }

    #[Override]
    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('Tambah & Buat Lagi');
    }

    #[Override]
    protected function getCreatedNotification(): ?Notification
    {
        return parent::getCreatedNotification()->title('Kategori berhasil dibuat');
    }
}
