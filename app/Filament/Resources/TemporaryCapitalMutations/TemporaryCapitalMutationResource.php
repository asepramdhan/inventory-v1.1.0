<?php

namespace App\Filament\Resources\TemporaryCapitalMutations;

use App\Filament\Resources\TemporaryCapitalMutations\Pages\ManageTemporaryCapitalMutations;
use App\Models\TemporaryCapitalMutation;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TemporaryCapitalMutationResource extends Resource
{
    protected static ?string $model = TemporaryCapitalMutation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'CapitalMutation';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('CapitalMutation')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('CapitalMutation'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('CapitalMutation')
            ->columns([
                TextColumn::make('CapitalMutation')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTemporaryCapitalMutations::route('/'),
        ];
    }
}
