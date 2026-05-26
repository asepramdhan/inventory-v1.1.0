<?php

namespace App\Filament\Resources\CapitalMutations\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CapitalMutationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->filtersFormColumns(2)
            ->deferFilters(false)
            ->persistFiltersInSession(true)
            ->persistSortInSession(true)
            ->deferLoading()
            ->columns([
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d-m-Y')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipe Mutasi')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'withdrawal' => 'success',         // Hijau untuk tarik dana masuk
                        'supplier_payment' => 'warning',   // Kuning/Oranye untuk bayar produsen
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'withdrawal' => 'Penarikan Saldo',
                        'supplier_payment' => 'Bayar Produsen',
                        default => ucfirst($state),
                    })
                    ->searchable(),

                TextColumn::make('store.shop_name')
                    ->label('Toko')
                    ->placeholder('Umum / Non-Toko')
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Nominal (IDR)')
                    ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                    ->sortable(),

                TextColumn::make('source')
                    ->label('Asal Dana')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('destination')
                    ->label('Tujuan Dana')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('payment_status')
                    ->label('Status Bayar')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'danger', // Merah jika utang/tempo ke produsen
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'paid' => 'Lunas',
                        'unpaid' => 'Tempo (Utang)',
                        default => ucfirst($state),
                    })
                    ->visible(fn($record) => $record?->type === 'supplier_payment' || $record === null),

                TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d-m-Y')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('reference_number')
                    ->label('No. Ref / Nota')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Filter Tipe Mutasi')
                    ->options([
                        'withdrawal' => 'Penarikan Saldo',
                        'supplier_payment' => 'Pembayaran Produsen',
                    ])
                    ->native(false),

                SelectFilter::make('store_id')
                    ->label('Filter Berdasarkan Toko')
                    // Tambahkan closure Builder di argumen ketiga untuk memfilter query toko
                    ->relationship(
                        'store',
                        'shop_name',
                        fn(Builder $query) => $query->where('user_id', Auth::user()->id),
                    )
                    ->preload()
                    ->native(false),
            ], layout: FiltersLayout::Modal)
            ->defaultSort('date', 'desc') // Mengurutkan dari mutasi tanggal terbaru
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make()
                        ->modalHeading('Hapus Mutasi'),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Hapus Mutasi Yang Dipilih'),
                    // Catatan: ForceDelete & Restore sengaja tidak dipasang jika tidak pakai SoftDeletes di Model.
                ]),
            ]);
    }
}
