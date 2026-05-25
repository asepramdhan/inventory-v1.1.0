<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItem;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestTransactions extends TableWidget
{
    protected ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Transaksi Terakhir';

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->query(fn(): Builder => TransactionItem::query())
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal Transaksi')
                    ->description(fn($record) => "Platform: " . ucfirst($record->transaction->store->platform))
                    ->dateTime('d M Y H:i')
                    ->color('gray'),
                TextColumn::make('product.name')
                    ->label('Nama Produk')
                    ->limit(20)
                    ->color('gray')
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    }),
                TextColumn::make('product.sku')
                    ->label('SKU Produk')
                    ->badge()
                    ->limit(20)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    })
                    ->color('danger'),
                TextColumn::make('price')
                    ->label('Harga Jual')
                    ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                    ->color('success')
                    ->weight('bold'),
                TextColumn::make('total_margin') // Beri nama unik agar tidak bentrok dengan nama field DB
                    ->label('Total Margin')
                    ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                    ->color('warning')
                    ->weight('bold')
                    ->getStateUsing(function (TransactionItem $record) {
                        // 1. Ambil data Store melalui relasi Transaction
                        $store = $record->transaction->store;

                        // Ambil nilai dasar
                        $subtotal = $record->price * $record->quantity;

                        // 2. Hitung Potongan Admin (Asumsi admin_fee di DB adalah angka persen, misal 5.5)
                        $adminFeePercent = (float) $store->admin_fee;
                        $adminFeeAmount = $subtotal * ($adminFeePercent / 100);

                        // 3. Potongan Tetap
                        $processingFee = (float) $store->processing_fee; // misal 1250
                        $extraFee = (float) $store->extra_fee;

                        // 4. Hitung Margin Akhir
                        $margin = $subtotal - $adminFeeAmount - $processingFee - $extraFee;

                        return $margin;
                    })
                    ->description(function (TransactionItem $record) {
                        $store = $record->transaction->store;
                        return "Fee: {$store->admin_fee}% + Rp" . number_format($store->processing_fee, 0, ',', '.');
                    }),
                TextColumn::make('quantity')
                    ->label('Qty')
                    ->badge()
                    ->color('info')
                    ->weight('bold'),
                TextColumn::make('transaction.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'diproses' => 'warning',
                        'dikirim' => 'info',
                        'selesai' => 'success',
                        'dibatalkan' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
