<?php

namespace App\Filament\Resources\AdsCosts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdsCostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            // ->filtersFormColumns(3)
            // ->deferFilters(false)
            // ->persistFiltersInSession(true)
            ->deferLoading()
            ->columns([
                TextColumn::make('store.shop_name')
                    ->label('Toko')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Biaya Iklan')
                    ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                    ->badge()
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d/m/y H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // SelectFilter::make('store_id')
                //     ->label('Filter Toko')
                //     ->options(
                //         Store::query()
                //             ->where('user_id', Auth::user()->id)
                //             ->pluck('shop_name', 'id')
                //     )
                //     ->searchable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton(),
                EditAction::make()
                    ->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('Hapus Biaya Iklan'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Hapus Biaya Iklan Yang Dipilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
