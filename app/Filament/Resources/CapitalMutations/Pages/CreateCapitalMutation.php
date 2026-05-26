<?php

namespace App\Filament\Resources\CapitalMutations\Pages;

use App\Filament\Resources\CapitalMutations\CapitalMutationResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Override;

class CreateCapitalMutation extends CreateRecord
{
    protected static string $resource = CapitalMutationResource::class;

    protected static ?string $title = 'Buat Mutasi Keuangan';

    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;

        return parent::mutateFormDataBeforeCreate($data);
    }

    #[Override]
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Buat Mutasi');
    }

    #[Override]
    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('Tambah & Buat Lagi');
    }

    #[Override]
    protected function getCreatedNotification(): ?Notification
    {
        return parent::getCreatedNotification()->title('Mutasi berhasil dibuat');
    }
}
