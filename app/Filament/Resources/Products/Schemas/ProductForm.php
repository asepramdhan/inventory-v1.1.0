<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Category;
use Illuminate\Support\Str;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;

class ProductForm
{
    public static function configure(Schema $schema): Schema
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
                    ->required(),
                Toggle::make('status')->label(fn($get) => $get('status') ? 'Aktif' : 'Tidak Aktif')
                    ->default(true)
                    ->columnStart(1)
            ]);
    }
}
