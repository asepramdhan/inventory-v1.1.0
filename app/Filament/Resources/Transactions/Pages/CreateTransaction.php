<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Override;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected static ?string $title = 'Buat Transaksi';

    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;

        return parent::mutateFormDataBeforeCreate($data);
    }

    #[Override]
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Buat Transaksi');
    }

    #[Override]
    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('Tambah & Buat Lagi');
    }

    #[Override]
    protected function preserveFormDataWhenCreatingAnother(array $data): array
    {
        return $data;
    }

    #[Override]
    protected function getCreatedNotification(): ?Notification
    {
        return parent::getCreatedNotification()->title('Transaksi berhasil dibuat');
    }
}
