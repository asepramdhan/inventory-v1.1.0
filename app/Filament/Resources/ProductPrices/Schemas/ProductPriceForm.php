<?php

namespace App\Filament\Resources\ProductPrices\Schemas;

use App\Models\Product;
use App\Models\Store;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ProductPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ->required()
                    ->numeric()
                    ->columnSpan(2)
                    ->prefix('IDR'),
            ])
            ->columns(8);
    }
}
