<?php

namespace App\Filament\Resources\TemporaryFinancialLogs;

use App\Filament\Resources\TemporaryFinancialLogs\Pages\ManageTemporaryFinancialLogs;
use App\Models\TemporaryFinancialLog;
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

class TemporaryFinancialLogResource extends Resource
{
    protected static ?string $model = TemporaryFinancialLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'FinancialLog';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('FinancialLog')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('FinancialLog'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('FinancialLog')
            ->columns([
                TextColumn::make('FinancialLog')
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
            'index' => ManageTemporaryFinancialLogs::route('/'),
        ];
    }
}
