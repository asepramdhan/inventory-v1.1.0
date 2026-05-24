<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Models\Store;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('items.product.image')
                    ->label('Gambar')
                    ->imageSize(45),
                TextColumn::make('order_number')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->placeholder('Tanpa No. Pesanan')
                    ->copyable()
                    ->copyMessage('No. Pesanan berhasil disalin')
                    ->copyMessageDuration(1500)
                    ->description(fn($record) => ucfirst($record->items->first()->product->sku)),

                // 1. Toko dibuat lebih menonjol
                TextColumn::make('store.shop_name')
                    ->label('Toko')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn($record) => "Platform: " . ucfirst($record->store->platform)),

                // 2. SKU Produk (Gunakan Badge & Limit)
                // TextColumn::make('items.product.sku')
                //     ->label('SKU Produk')
                //     ->badge() // Mengubah teks jadi kapsul/tag agar rapi
                //     ->color('gray')
                //     ->listWithLineBreaks()
                //     ->limitList(2) // Hanya tampilkan 2 SKU awal
                //     ->expandableLimitedList() // Bisa di-klik untuk lihat sisanya (UX Mantap!)
                //     ->searchable(),

                // 3. Total Harga (Dibuat tebal & warna hijau agar kontras)
                TextColumn::make('total_price')
                    ->label('Total')
                    ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                    ->color('success') // Warna hijau
                    ->weight('bold')
                    ->sortable()
                    ->alignment('end'), // Rata kanan agar angka mudah dibandingkan

                // 4. QTY (Gunakan warna yang berbeda untuk membedakan dengan harga)
                TextColumn::make('items_sum_quantity')
                    ->sum('items', 'quantity')
                    ->label('QTY')
                    ->badge()
                    ->color('info') // Warna biru
                    ->sortable()
                    ->alignment('center'),

                // 5. Waktu Transaksi (Format lebih ringkas)
                TextColumn::make('created_at')
                    ->label('Waktu Transaksi')
                    ->dateTime('d/m/y H:i')
                    ->timezone('Asia/Jakarta')
                    ->sortable()
                    ->color('gray'),

                TextColumn::make('status')
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
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),
            ])
            ->filters([
                // 1. Filter Toko (Tetap dipertahankan)
                SelectFilter::make('store_id')
                    ->label('Filter Toko')
                    ->options(
                        Store::query()
                            ->where('user_id', Auth::user()->id)
                            ->pluck('shop_name', 'id')
                    )
                    ->searchable(),

                // 2. Filter Tanggal / Periode
                Filter::make('created_at')
                    ->form([
                        Select::make('period')
                            ->label('Periode Waktu')
                            ->options([
                                'today' => 'Hari Ini',
                                'yesterday' => 'Kemarin',
                                'this_week' => 'Minggu Ini',
                                'this_month' => 'Bulan Ini',
                                'custom' => 'Pilih Tanggal Sendiri',
                            ])
                            ->reactive(),

                        DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->hidden(fn(Get $get) => $get('period') !== 'custom'),

                        DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->hidden(fn(Get $get) => $get('period') !== 'custom')
                            ->default(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['period'] === 'today', fn($q) => $q->whereDate('created_at', Carbon::today()))
                            ->when($data['period'] === 'yesterday', fn($q) => $q->whereDate('created_at', Carbon::yesterday()))
                            ->when($data['period'] === 'this_week', fn($q) => $q->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]))
                            ->when($data['period'] === 'this_month', fn($q) => $q->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year))
                            ->when($data['period'] === 'custom', function ($q) use ($data) {
                                return $q->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                                    ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']));
                            });
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['period'] ?? null) {
                            $labels = ['today' => 'Hari Ini', 'yesterday' => 'Kemarin', 'this_week' => 'Minggu Ini', 'this_month' => 'Bulan Ini', 'custom' => 'Custom'];
                            $indicators[] = 'Waktu: ' . $labels[$data['period']];
                        }
                        return $indicators;
                    }),
                SelectFilter::make('status')
                    ->label('Status Pesanan')
                    ->options([
                        'pending' => 'Menunggu Pembayaran',
                        'diproses' => 'Sedang Diproses',
                        'dikirim' => 'Dalam Pengiriman',
                        'selesai' => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                    ])
                    ->indicator('Status') // Menampilkan label filter saat aktif di atas tabel
                    ->native(false), // Menggunakan interface dropdown Filament yang lebih modern
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(3)
            ->deferFilters(false)
            ->persistFiltersInSession(true)
            ->recordActions([
                ActionGroup::make([
                    Action::make('setStatuSent')
                        ->label('Set Dikirim')
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->hidden(fn($record) => $record->status !== 'diproses') // Hanya muncul jika status masih diproses
                        ->action(fn($record) => $record->update(['status' => 'dikirim']))
                        ->requiresConfirmation(),
                    Action::make('setStatuSelesai')
                        ->label('Set Selesai')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->hidden(fn($record) => $record->status !== 'dikirim') // Hanya muncul jika status masih dikirim
                        ->action(fn($record) => $record->update(['status' => 'selesai']))
                        ->requiresConfirmation(),
                    EditAction::make(),
                    DeleteAction::make()
                        ->modalHeading('Hapus Transaksi'),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('updateStatus')
                        ->label('Ubah Status Massal')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->form([
                            Select::make('status')
                                ->label('Pilih Status Baru')
                                ->options([
                                    'pending' => 'Menunggu Pembayaran',
                                    'diproses' => 'Sedang Diproses',
                                    'dikirim' => 'Dalam Pengiriman',
                                    'selesai' => 'Selesai',
                                    'dibatalkan' => 'Dibatalkan',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function ($record) use ($data) {
                                $record->update(['status' => $data['status']]);
                            });
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                    DeleteBulkAction::make()
                        ->modalHeading('Hapus Transaksi Yang Dipilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped(); // Membuat baris tabel selang-seling warna agar mudah dibaca
    }
}
