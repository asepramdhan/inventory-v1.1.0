<?php

namespace App\Filament\Resources\AdsCosts\Schemas;

use App\Models\Store;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
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
                    ->columnSpan(2)
                    ->numeric()
                    ->default(0.0),
            ])
            ->columns(6);
    }
}
