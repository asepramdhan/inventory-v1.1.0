<?php

namespace App\Filament\Resources\Stores\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class StoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('platform')
                    ->label('Platform')
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->searchable(),
                TextColumn::make('shop_name')
                    ->label('Nama Toko')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                ColumnGroup::make('Biaya Operasional')
                    ->columns([
                        TextColumn::make('admin_fee')
                            ->label('Admin (%)')
                            ->suffix('%')
                            ->badge()
                            ->color('info')
                            ->alignment('center'),
                        TextColumn::make('processing_fee')
                            ->label('Proses (Rp)')
                            ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                            ->color('warning')
                            ->badge()
                            ->alignment('center'),
                        TextColumn::make('extra_fee')
                            ->label('Lainnya (Rp)')
                            ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                            ->color('danger')
                            ->badge()
                            ->alignment('center'),
                    ])
                    ->alignment('center'),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/y H:i')
                    ->timezone('Asia/Jakarta')
                    ->sortable()
                    ->color('gray'),
                ToggleColumn::make('status')
                    ->label('Status')
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton()
                    ->modalHeading('Hapus Toko'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Hapus Toko Yang Dipilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
