<?php

namespace App\Filament\Resources\CapitalMutations;

use App\Filament\Resources\CapitalMutations\Pages\CreateCapitalMutation;
use App\Filament\Resources\CapitalMutations\Pages\EditCapitalMutation;
use App\Filament\Resources\CapitalMutations\Pages\ListCapitalMutations;
use App\Filament\Resources\CapitalMutations\Schemas\CapitalMutationForm;
use App\Filament\Resources\CapitalMutations\Tables\CapitalMutationsTable;
use App\Models\CapitalMutation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

class CapitalMutationResource extends Resource
{
    protected static ?string $model = CapitalMutation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan & Analisa';

    protected static ?string $navigationLabel = 'Mutasi';

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::user()->id);
    }

    public static function form(Schema $schema): Schema
    {
        return CapitalMutationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CapitalMutationsTable::configure($table);
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
            'index' => ListCapitalMutations::route('/'),
            'create' => CreateCapitalMutation::route('/create'),
            'edit' => EditCapitalMutation::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
