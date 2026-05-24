<?php

namespace App\Filament\Resources\AdsCosts;

use App\Filament\Resources\AdsCosts\Pages\CreateAdsCost;
use App\Filament\Resources\AdsCosts\Pages\EditAdsCost;
use App\Filament\Resources\AdsCosts\Pages\ListAdsCosts;
use App\Filament\Resources\AdsCosts\Pages\ViewAdsCost;
use App\Filament\Resources\AdsCosts\Schemas\AdsCostForm;
use App\Filament\Resources\AdsCosts\Schemas\AdsCostInfolist;
use App\Filament\Resources\AdsCosts\Tables\AdsCostsTable;
use App\Models\AdsCost;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

class AdsCostResource extends Resource
{
    protected static ?string $model = AdsCost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan & Analisa';

    protected static ?string $navigationLabel = 'Biaya Iklan';

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::user()->id);
    }

    public static function form(Schema $schema): Schema
    {
        return AdsCostForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdsCostInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdsCostsTable::configure($table);
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
            'index' => ListAdsCosts::route('/'),
            'create' => CreateAdsCost::route('/create'),
            'view' => ViewAdsCost::route('/{record}'),
            'edit' => EditAdsCost::route('/{record}/edit'),
        ];
    }
}
