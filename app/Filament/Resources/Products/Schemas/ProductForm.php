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
use Illuminate\Support\Facades\Auth;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')->default(Auth::user()->id),
                TextInput::make('name')->label('Nama Produk')
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
                    ->numeric()
                    ->required(),
                FileUpload::make('image')->label('Gambar')
                    ->image()
                    ->required(),
                Toggle::make('status')->label(fn($get) => $get('status') ? 'Aktif' : 'Tidak Aktif')
                    ->default(true)
                    ->columnStart(1)
            ]);
    }
}
