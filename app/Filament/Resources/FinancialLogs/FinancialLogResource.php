<?php

namespace App\Filament\Resources\FinancialLogs;

use App\Filament\Resources\FinancialLogs\Pages\CreateFinancialLog;
use App\Filament\Resources\FinancialLogs\Pages\EditFinancialLog;
use App\Filament\Resources\FinancialLogs\Pages\ListFinancialLogs;
use App\Filament\Resources\FinancialLogs\Schemas\FinancialLogForm;
use App\Filament\Resources\FinancialLogs\Tables\FinancialLogsTable;
use App\Models\FinancialLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

class FinancialLogResource extends Resource
{
    protected static ?string $model = FinancialLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan & Analisa';

    protected static ?string $recordTitleAttribute = 'FinancialLog';

    protected static ?string $navigationLabel = 'Keuangan';

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::user()->id);
    }

    public static function form(Schema $schema): Schema
    {
        return FinancialLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinancialLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFinancialLogs::route('/'),
            'create' => CreateFinancialLog::route('/create'),
            'edit' => EditFinancialLog::route('/{record}/edit'),
        ];
    }
}
