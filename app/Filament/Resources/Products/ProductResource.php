<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\Category;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = ' Produk';

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
                TextInput::make('name')->label('Nama Produk')
                    ->autofocus()
                    ->required(),
                Select::make('category_id')
                    ->label('Kategori')
                    ->options(
                        Category::query()
                            ->where('user_id', Auth::user()->id)
                            ->where('status', true)
                            ->pluck('name', 'id')
                            ->map(fn($name) => Str::title($name))
                    )
                    ->searchable()
                    ->required(),
                TextInput::make('sku')->label('SKU')->required(),
                TextInput::make('price')->label('Harga Jual')
                    ->prefix('Rp ')
                    ->required()
                    ->extraInputAttributes(['type' => 'text', 'inputmode' => 'numeric'])
                    ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))

                    // PERBAIKAN: Buang koma (,), atau bersihkan semua karakter non-digit agar aman 100%
                    ->dehydrateStateUsing(function ($state) {
                        if (! $state) return null;

                        // Menghapus semua karakter selain angka murni (termasuk titik atau koma)
                        return preg_replace('/[^0-9]/', '', $state);
                    }),
                FileUpload::make('image')->label('Gambar')
                    ->image()
                    ->columnSpanFull()
                    ->required(),
                Toggle::make('status')->label(fn($get) => $get('status') ? 'Aktif' : 'Tidak Aktif')
                    ->default(true)
                    ->columnStart(1)
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 2])->columnSpanFull()
                    ->schema([
                        ImageEntry::make('image')->label('Gambar')
                            ->columnSpanFull()
                            ->imageSize(250),
                        TextEntry::make('name')->label('Nama Produk'),
                        TextEntry::make('category.name')->label('Kategori')
                            ->badge()
                            ->formatStateUsing(fn($state) => ucwords($state)),
                        TextEntry::make('sku')->label('SKU Produk'),
                        TextEntry::make('price')->label('Harga')
                            ->money('IDR', locale: 'id_ID', decimalPlaces: 0),
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M y H:i')
                            ->timezone('Asia/Jakarta'),
                        TextEntry::make('updated_at')
                            ->label('Diubah')
                            ->dateTime('d M y H:i')
                            ->timezone('Asia/Jakarta'),
                        IconEntry::make('status')->label('Status')
                            ->color(fn($get) => $get('status') ? 'success' : 'danger')
                            ->icon(fn($get) => $get('status') ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Product')
            ->deferLoading()
            ->columns([
                ImageColumn::make('image')->label('Gambar')
                    ->imageSize(55),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M y H:i')
                    ->timezone('Asia/Jakarta')
                    ->sortable()
                    ->color('gray'),
                TextColumn::make('name')->label('Nama Produk')
                    ->limit(35)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    })
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucwords($state))
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                    ->color('success') // Warna hijau
                    ->weight('bold')
                    ->sortable()
                    ->alignment('end'),
                ToggleColumn::make('status')->label('Status'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->modalHeading('Detail Produk')
                    ->modalDescription('Detail produk marketplace toko Anda.')
                    ->modalWidth('lg')
                    ->slideOver(),
                EditAction::make()
                    ->iconButton()
                    ->modalHeading('Edit Produk')
                    ->modalDescription('Pastikan nama produk belum terdaftar sebelumnya.')
                    ->modalWidth('lg')
                    ->slideOver(),
                DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('Hapus Produk'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Hapus Produk Yang Dipilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProducts::route('/'),
        ];
    }
}
