<?php

namespace App\Filament\Resources\AdsCosts\Pages;

use App\Filament\Resources\AdsCosts\AdsCostResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Override;

class CreateAdsCost extends CreateRecord
{
    protected static ?string $title = 'Buat Biaya Iklan';

    protected static string $resource = AdsCostResource::class;

    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;

        return parent::mutateFormDataBeforeCreate($data);
    }

    #[Override]
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Buat Biaya Iklan');
    }

    #[Override]
    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('Tambah & Buat Biaya Iklan Lagi');
    }
}
