<?php

namespace App\Filament\Widgets;

use App\Models\ProductPrice;
use App\Models\TransactionItem;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LatestTransactions extends TableWidget
{
    use InteractsWithPageFilters; // Mengizinkan tabel membaca filter global dari halaman induk

    protected ?string $pollingInterval = null;
    protected static bool $isLazy = false;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Rincian Transaksi & Margin Produk';

    public function table(Table $table): Table
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $storeId = $this->filters['storeId'] ?? null;
        $userId = Auth::id();

        return $table
            ->deferLoading()
            ->query(
                fn(): Builder => TransactionItem::query()
                    ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                    ->where('transactions.user_id', $userId)
                    ->when($startDate, fn($q) => $q->whereDate('transactions.created_at', '>=', $startDate))
                    ->when($endDate, fn($q) => $q->whereDate('transactions.created_at', '<=', $endDate))
                    ->when($storeId, fn($q) => $q->where('transactions.store_id', $storeId))
                    ->select('transaction_items.*') // Menghindari ambiguitas ID kolom
            )
            ->columns([
                TextColumn::make('transaction.created_at') // <--- DIUBAH dari 'transactions.created_at' menjadi 'transaction.created_at'
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->timezone('Asia/Jakarta') // Memastikan timezone sesuai WIB
                    ->description(fn($record) => "Toko: " . $record->transaction->store->shop_name)
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('product.name')
                    ->label('Produk / SKU')
                    ->weight('medium')
                    ->description(fn($record) => "SKU: " . ($record->product?->sku ?? '-')),

                TextColumn::make('price')
                    ->label('Harga Jual')
                    ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                    ->color('success')
                    ->alignEnd(),

                // FIX RUMUS: Total Margin Sekarang Mengurangi HPP secara Akurat!
                TextColumn::make('total_margin')
                    ->label('Margin Bersih')
                    ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                    ->weight('bold')
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger')
                    ->alignEnd()
                    ->getStateUsing(function (TransactionItem $record) {
                        $store = $record->transaction->store;
                        $subtotal = $record->price * $record->quantity;

                        // 1. Ambil HPP produk pada toko terkait
                        $hppUnit = ProductPrice::where('product_id', $record->product_id)
                            ->where('store_id', $store->id)
                            ->value('price') ?? 0;
                        $totalHpp = $hppUnit * $record->quantity;

                        // 2. Hitung Potongan Admin Marketplace
                        $adminFeeAmount = $subtotal * ((float) $store->admin_fee / 100);

                        // 3. Potongan Tetap Transaksi
                        $processingFee = (float) $store->processing_fee;
                        $extraFee = (float) $store->extra_fee;

                        // 4. HASIL AKHIR: Jual - Modal - Potongan Marketplace
                        return $subtotal - $totalHpp - $adminFeeAmount - $processingFee - $extraFee;
                    })
                    ->description(function (TransactionItem $record) {
                        // Tampilkan modal HPP per unit di deskripsi bawah kecil untuk mempermudah audit owner
                        $store = $record->transaction->store;
                        $hppUnit = ProductPrice::where('product_id', $record->product_id)
                            ->where('store_id', $store->id)
                            ->value('price') ?? 0;
                        return "HPP Unit: Rp" . number_format($hppUnit, 0, ',', '.');
                    }),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

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
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),
            ])
            ->striped()
            ->defaultSort('created_at', 'desc');
    }
}
