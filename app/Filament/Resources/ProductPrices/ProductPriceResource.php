<?php

namespace App\Filament\Resources\ProductPrices;

use App\Filament\Resources\ProductPrices\Pages\ManageProductPrices;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Store;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

class ProductPriceResource extends Resource
{
    protected static ?string $model = ProductPrice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = ' HPP Produk';

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
                Select::make('product_id')
                    ->label('Nama Produk')
                    ->options(
                        Product::query()
                            ->where('user_id', Auth::user()->id)
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->columnSpan(4)
                    ->required(),
                Select::make('store_id')
                    ->label('Diupload Ke Toko')
                    ->options(
                        Store::query()
                            ->where('user_id', Auth::user()->id)
                            ->pluck('shop_name', 'id')
                    )
                    ->searchable()
                    ->columnSpan(2)
                    ->required(),
                TextInput::make('price')
                    ->label('HPP Produk')
                    ->autofocus()
                    ->required()
                    ->columnSpan(2)
                    ->prefix('IDR')
                    ->default(0)
                    ->extraInputAttributes(['type' => 'text', 'inputmode' => 'numeric'])
                    ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))

                    // PERBAIKAN: Buang koma (,), atau bersihkan semua karakter non-digit agar aman 100%
                    ->dehydrateStateUsing(function ($state) {
                        if (! $state) return 0;

                        // Menghapus semua karakter selain angka murni (termasuk titik atau koma)
                        return preg_replace('/[^0-9]/', '', $state);
                    }),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 2])->columnSpanFull()
                    ->schema([
                        ImageEntry::make('product.image')->label('Gambar')
                            ->columnSpanFull()
                            ->imageSize(250),
                        TextEntry::make('product.name')->label('Nama Produk'),
                        TextEntry::make('product.sku')->label('SKU Produk')
                            ->badge(),
                        TextEntry::make('store.shop_name')->label('Toko Terkait'),
                        TextEntry::make('price')->label('Harga HPP Produk')
                            ->badge()
                            ->color('success')
                            ->money('IDR', locale: 'id_ID', decimalPlaces: 0),
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
            ->recordTitleAttribute('ProductPrice')
            ->deferLoading()
            ->columns([
                ImageColumn::make('product.image')->label('Gambar')
                    ->imageSize(55),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M y H:i')
                    ->sortable()
                    ->color('gray'),
                TextColumn::make('product.name')
                    ->label('Nama Produk')
                    ->limit(35)
                    ->tooltip(function (TextColumn $column): ?string {
                        return $column->getRecord()->product->name;
                    })
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('store.shop_name')
                    ->label('Toko Terkait')
                    ->badge(),
                TextColumn::make('price')
                    ->label('HPP Produk')
                    ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                    ->color('success') // Warna hijau
                    ->weight('bold')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->modalHeading('Detail HPP Produk')
                    ->modalDescription('Detail HPP produk marketplace toko Anda.')
                    ->modalWidth('lg')
                    ->slideOver(),
                EditAction::make()
                    ->iconButton()
                    ->modalHeading('Edit HPP Produk')
                    ->modalDescription('Pastikan nama hpp produk belum terdaftar sebelumnya.')
                    ->modalWidth('lg')
                    ->slideOver(),
                DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('Hapus HPP Produk'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Hapus HPP Produk Yang Dipilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProductPrices::route('/'),
        ];
    }
}
