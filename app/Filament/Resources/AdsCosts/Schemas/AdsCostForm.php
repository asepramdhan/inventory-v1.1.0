<?php

namespace App\Filament\Resources\AdsCosts\Schemas;

use App\Models\Store;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;

class AdsCostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DateTimePicker::make('created_at')
                    ->label('Tanggal')
                    ->default(now())
                    ->columnSpan(2)
                    ->required(),
                Select::make('store_id')
                    ->label('Toko')
                    ->options(
                        Store::query()
                            ->where('user_id', Auth::user()->id)
                            ->pluck('shop_name', 'id')
                    )
                    ->columnSpan(2)
                    ->searchable()
                    ->required(),
                TextInput::make('amount')
                    ->label('Biaya Iklan')
                    ->required()
                    ->autofocus()
                    ->columnSpan(2)
                    ->extraInputAttributes(['type' => 'text', 'inputmode' => 'numeric'])
                    ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))

                    // PERBAIKAN: Buang koma (,), atau bersihkan semua karakter non-digit agar aman 100%
                    ->dehydrateStateUsing(function ($state) {
                        if (! $state) return null;

                        // Menghapus semua karakter selain angka murni (termasuk titik atau koma)
                        return preg_replace('/[^0-9]/', '', $state);
                    }),
            ])
            ->columns(6);
    }
}
