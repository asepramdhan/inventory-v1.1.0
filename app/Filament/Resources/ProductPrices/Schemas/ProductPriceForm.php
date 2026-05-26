<?php

namespace App\Filament\Resources\ProductPrices\Schemas;

use App\Models\Product;
use App\Models\Store;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
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
                    ->autofocus()
                    ->required()
                    ->columnSpan(2)
                    ->prefix('IDR')
                    ->extraInputAttributes(['type' => 'text', 'inputmode' => 'numeric'])
                    ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))

                    // PERBAIKAN: Buang koma (,), atau bersihkan semua karakter non-digit agar aman 100%
                    ->dehydrateStateUsing(function ($state) {
                        if (! $state) return null;

                        // Menghapus semua karakter selain angka murni (termasuk titik atau koma)
                        return preg_replace('/[^0-9]/', '', $state);
                    }),
            ])
            ->columns(8);
    }
}
