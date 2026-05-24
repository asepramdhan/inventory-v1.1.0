<?php

namespace App\Filament\Resources\AdsCosts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AdsCostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('store.shop_name')
                    ->label('Nama Toko'),
                TextEntry::make('amount')
                    ->label('Biaya Iklan')
                    ->money('IDR', locale: 'id_ID', decimalPlaces: 0),
                TextEntry::make('created_at')
                    ->label('Dibuat pada')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Diubah pada')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
            ]);
    }
}
