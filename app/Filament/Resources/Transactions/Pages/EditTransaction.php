<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected static ?string $title = 'Ubah Transaksi';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->modalHeading('Hapus Transaksi'),
        ];
    }
}
