<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Kategori';

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::user()->id);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')->default(Auth::user()->id),
                TextInput::make('name')->label('Nama Kategori')
                    ->columnSpanFull()
                    ->autofocus()
                    ->required(),
                Toggle::make('status')->label(fn($get) => $get('status') ? 'Aktif' : 'Tidak Aktif')
                    ->default(true)
                    ->columnStart(1)
                    ->required(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 2])->columnSpanFull()
                    ->schema([
                        TextEntry::make('name')->label('Nama')
                            ->formatStateUsing(fn($state) => ucwords($state)),
                        IconEntry::make('status')
                            ->label('Status')
                            ->color(fn($get) => $get('status') ? 'success' : 'danger')
                            ->icon(fn($get) => $get('status') ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M y H:i')
                            ->timezone('Asia/Jakarta'),
                        TextEntry::make('updated_at')
                            ->label('Diubah')
                            ->dateTime('d M y H:i')
                            ->timezone('Asia/Jakarta'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Category')
            ->deferLoading()
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d M y H:i')
                    ->timezone('Asia/Jakarta')
                    ->sortable()
                    ->color('gray'),
                TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->weight('bold')
                    ->formatStateUsing(fn($state) => ucwords($state))
                    ->searchable(),
                ToggleColumn::make('status')
                    ->label('Status'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->modalHeading('Detail Kategori')
                    ->modalDescription('Detail Kategori Produk')
                    ->modalWidth('md')
                    ->slideOver(),
                EditAction::make()
                    ->iconButton()
                    ->modalHeading('Edit Kategori')
                    ->modalDescription('Pastikan nama kategori belum terdaftar sebelumnya.')
                    ->modalWidth('md')
                    ->slideOver(),
                DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('Hapus Kategori'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Hapus Toko Yang Dipilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }
}
