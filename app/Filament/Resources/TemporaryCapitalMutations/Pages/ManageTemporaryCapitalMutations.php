<?php

namespace App\Filament\Resources\TemporaryCapitalMutations\Pages;

use App\Filament\Resources\TemporaryCapitalMutations\TemporaryCapitalMutationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTemporaryCapitalMutations extends ManageRecords
{
    protected static string $resource = TemporaryCapitalMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
