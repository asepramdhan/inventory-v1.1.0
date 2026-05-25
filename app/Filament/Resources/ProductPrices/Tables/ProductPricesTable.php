<?php

namespace App\Filament\Resources\ProductPrices\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class ProductPricesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('store.shop_name')
                    ->label('Toko'),
            ])
            ->defaultGroup('store.shop_name')
            ->deferLoading()
            ->columns([
                ImageColumn::make('product.image')->label('Gambar')
                    ->imageSize(50),
                TextColumn::make('product.name')
                    ->label('Nama Produk')
                    ->limit(20)
                    ->tooltip(function (TextColumn $column): ?string {
                        return $column->getRecord()->product->name;
                    })
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('product.sku')
                    ->label('SKU Produk')
                    ->badge() // Mengubah teks jadi kapsul/tag agar rapi
                    ->color('gray')
                    ->limit(15)
                    ->searchable(),
                TextColumn::make('store.shop_name')
                    ->label('Toko'),
                TextColumn::make('price')
                    ->label('HPP')
                    ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                    ->color('success') // Warna hijau
                    ->weight('bold')
                    ->sortable()
                    ->alignment('end'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/y H:i')
                    ->sortable()
                    ->color('gray')
                    ->description(fn($record) => $record->updated_at->format('d/m/y H:i')),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->modalHeading('Hapus HPP Produk'),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Hapus HPP Produk Yang Dipilih'),
                ]),
            ])
            ->searchable()
            ->striped()
            ->defaultSort('created_at', 'desc');
    }
}
