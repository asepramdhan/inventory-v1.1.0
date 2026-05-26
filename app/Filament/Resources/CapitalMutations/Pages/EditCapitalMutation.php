<?php

namespace App\Filament\Resources\CapitalMutations\Pages;

use App\Filament\Resources\CapitalMutations\CapitalMutationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCapitalMutation extends EditRecord
{
    protected static string $resource = CapitalMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
