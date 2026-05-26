<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')->default(Auth::user()->id),
                Select::make('platform')
                    ->label('Platform')
                    ->options([
                        'shopee' => 'Shopee',
                        'lazada' => 'Lazada',
                        'tokopedia' => 'Tokopedia',
                        'blibli' => 'Blibli',
                        'bukalapak' => 'Bukalapak',
                        'tiktokshop' => 'Tiktokshop',
                        'offline' => 'Offline / Toko Fisik',
                    ])
                    ->searchable()
                    ->required(),
                TextInput::make('shop_name')
                    ->label('Nama Toko')
                    ->autofocus()
                    ->required(),
                Section::make()
                    ->schema([
                        TextInput::make('admin_fee')
                            ->label('Biaya Admin (%)')
                            ->numeric()
                            ->columnSpan(1)
                            ->required(),
                        TextInput::make('processing_fee')
                            ->label('Biaya Proses Per Pesanan')
                            ->columnSpan(1)
                            ->required()
                            ->extraInputAttributes(['type' => 'text', 'inputmode' => 'numeric'])
                            ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))

                            // PERBAIKAN: Buang koma (,), atau bersihkan semua karakter non-digit agar aman 100%
                            ->dehydrateStateUsing(function ($state) {
                                if (! $state) return null;

                                // Menghapus semua karakter selain angka murni (termasuk titik atau koma)
                                return preg_replace('/[^0-9]/', '', $state);
                            }),
                        TextInput::make('extra_fee')
                            ->label('Biaya Lainnya (nominal)')
                            ->columnSpan(1)
                            ->numeric()
                            ->default(0)
                            ->required(),
                        // ->extraInputAttributes(['type' => 'text', 'inputmode' => 'numeric'])
                        // ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))

                        // // PERBAIKAN: Buang koma (,), atau bersihkan semua karakter non-digit agar aman 100%
                        // ->dehydrateStateUsing(function ($state) {
                        //     if (! $state) return null;

                        //     // Menghapus semua karakter selain angka murni (termasuk titik atau koma)
                        //     return preg_replace('/[^0-9]/', '', $state);
                        // }),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->compact(),
                Toggle::make('status')
                    // buat kondisi label agar bisa dinamis
                    ->label(fn($get) => $get('status') ? 'Aktif' : 'Tidak Aktif')
                    ->default(true)
                    ->columnStart(1)
                    ->required(),
            ]);
    }
}
