<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\ManageTransactions;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transaction;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Override;
use UnitEnum;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan & Analisa';

    protected static ?string $navigationLabel = ' Transaksi';

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::user()->id);
    }

    public static function updateTotalPrice(callable $set, callable $get): void
    {
        $items = $get('items') ?? [];
        $total = 0;

        foreach ($items as $item) {
            $rawPrice = $item['price'] ?? 0;
            $cleanPrice = is_string($rawPrice) ? preg_replace('/[^0-9]/', '', $rawPrice) : $rawPrice;

            $price = floatval($cleanPrice ?? 0);
            $quantity = intval($item['quantity'] ?? 1);
            $total += $price * $quantity;
        }

        $set('total_price', $total);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')->default(Auth::user()->id),
                Select::make('store_id')
                    ->label('Toko / Marketplace')
                    ->relationship(
                        'store',
                        'shop_name',
                        fn(Builder $query) => $query->where('user_id', Auth::id())
                    )
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),

                TextInput::make('order_number')
                    ->label('Nomor Pesanan')
                    ->placeholder('Contoh: ORD-2026001')
                    ->autofocus(),

                Select::make('status')
                    ->label('Status Pesanan')
                    ->options([
                        'pending' => 'Menunggu Pembayaran',
                        'diproses' => 'Sedang Diproses',
                        'dikirim' => 'Dalam Pengiriman',
                        'selesai' => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                    ])
                    ->default('diproses')
                    ->native(false)
                    ->required(),

                Repeater::make('items')
                    ->relationship('items')
                    ->label('')
                    ->live()
                    ->afterStateUpdated(function (callable $set, callable $get) {
                        self::updateTotalPrice($set, $get);
                    })
                    ->addAction(function (Action $action) {
                        return $action
                            ->label('Tambah Produk Baru')
                            ->color('gray')
                            ->extraAttributes([
                                // 'onclick' => "setTimeout(() => { window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' }); }, 200)",
                            ]);
                    })
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk (SKU)')
                            ->relationship(
                                'product',
                                'sku',
                                fn(Builder $query) => $query->where('user_id', Auth::id())->where('status', true)
                            )
                            ->searchable()
                            ->preload()
                            ->columnSpan(4)
                            ->native(false)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $product = Product::find($state);
                                if ($product) {
                                    $set('price', $product->price);
                                }
                                self::updateTotalPrice($set, $get);
                            }),

                        TextInput::make('price')
                            ->label('Harga')
                            ->columnSpan(2)
                            ->required()
                            ->prefix('Rp')
                            ->extraInputAttributes(['type' => 'text', 'inputmode' => 'numeric'])
                            ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))
                            ->live()
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                self::updateTotalPrice($set, $get);
                            })
                            ->dehydrateStateUsing(function ($state) {
                                if (! $state) return null;
                                return preg_replace('/[^0-9]/', '', $state);
                            })
                            // KUSTOMISASI: Memberikan space jarak atas agar tidak dempet dengan pilihan produk
                            ->extraAttributes(['class' => 'mt-3']),

                        TextInput::make('quantity')
                            ->label('Qty')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->columnSpan(1)
                            ->minValue(1)
                            ->live()
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                self::updateTotalPrice($set, $get);
                            })
                            // KUSTOMISASI: Memberikan space jarak atas agar tidak dempet dengan input harga
                            ->extraAttributes(['class' => 'mt-3']),
                    ])
                    ->columnSpanFull()
                    ->columns(7)
                    ->defaultItems(1)
                    ->addActionAlignment(Alignment::Start)
                    ->collapsible()
                    ->cloneable()
                    ->itemNumbers(),

                Hidden::make('total_price')->default(0),

                Placeholder::make('total_price_display')
                    ->label('')
                    ->content(function (callable $get) {
                        $totalPrice = $get('total_price') ?? 0;
                        $totalFormatted = number_format($totalPrice, 0, ',', '.');

                        return new HtmlString("
                        <div class='flex flex-col items-end justify-center p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700/50 mt-2'>
                            <span class='text-xs font-semibold text-gray-400 uppercase tracking-wider'>Grand Total</span>
                            <span class='text-3xl font-black text-primary-600 dark:text-primary-400 tracking-tight mt-1'>Rp $totalFormatted</span>
                        </div>
                    ");
                    }),
            ])
            ->columns(3);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 2,
                    'lg' => 3,
                ])
                    ->columnSpanFull()
                    ->schema([
                        ImageEntry::make('items.product.image')
                            ->label('Gambar')
                            ->columnSpanFull()
                            ->stacked()
                            ->limit(3)
                            ->limitedRemainingText()
                            ->imageSize(150),
                        TextEntry::make('created_at')
                            ->label('Tanggal Transaksi')
                            ->columnSpan(1)
                            ->dateTime('d M y H:i')
                            ->timezone('Asia/Jakarta'),
                        TextEntry::make('store.shop_name')
                            ->label('Toko')
                            ->columnSpan(1),
                        TextEntry::make('order_number')
                            ->label('No. Pesanan')
                            ->columnSpan(1)
                            ->copyable()
                            ->copyMessage('No. Pesanan berhasil disalin')
                            ->copyMessageDuration(1500),
                        TextEntry::make('items.product.name')
                            ->label('Nama Produk')
                            ->columnSpanFull()
                            ->limit(35),
                        TextEntry::make('items.product.sku')
                            ->label('SKU Produk')
                            ->badge(),
                        TextEntry::make('items_sum_quantity')
                            ->sum('items', 'quantity')
                            ->label('Qty')
                            ->badge(),
                        TextEntry::make('total_price')
                            ->label('Grand Total')
                            ->badge()
                            ->color('success')
                            ->money('IDR', locale: 'id_ID', decimalPlaces: 0),
                        TextEntry::make('status')
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
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d M y H:i')
                            ->timezone('Asia/Jakarta'),
                        TextEntry::make('updated_at')
                            ->label('Diubah')
                            ->dateTime('d M y H:i')
                            ->timezone('Asia/Jakarta'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Transaction')
            ->filtersFormColumns(2)
            ->deferFilters(false)
            ->persistFiltersInSession(true)
            ->persistSortInSession(true)
            ->deferLoading()
            ->columns([
                ImageColumn::make('items.product.image')
                    ->label('Gambar')
                    ->stacked()
                    ->limit(1)
                    ->limitedRemainingText()
                    ->imageSize(45),

                // 5. Waktu Transaksi (Format lebih ringkas)
                TextColumn::make('created_at')
                    ->label('Tanggal Transaksi')
                    ->dateTime('d M y H:i')
                    ->timezone('Asia/Jakarta')
                    ->sortable()
                    ->color('gray'),

                TextColumn::make('order_number')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->placeholder('Tanpa No. Pesanan')
                    ->copyable()
                    ->copyMessage('No. Pesanan berhasil disalin')
                    ->copyMessageDuration(1500)
                    ->description(fn($record) => ucfirst(substr($record->items->first()->product->sku, 15))),

                // 3. Total Harga (Dibuat tebal & warna hijau agar kontras)
                TextColumn::make('total_price')
                    ->label('Total Harga')
                    ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                    ->color('success') // Warna hijau
                    ->weight('bold')
                    ->sortable(),

                // 4. QTY (Gunakan warna yang berbeda untuk membedakan dengan harga)
                TextColumn::make('items_sum_quantity')
                    ->sum('items', 'quantity')
                    ->label('QTY')
                    ->badge()
                    ->color('info') // Warna biru
                    ->sortable(),

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
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                Action::make('setStatuSent')
                    ->iconButton()
                    ->modalHeading('Konfirmasi Pengiriman')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->hidden(fn($record) => $record->status !== 'diproses') // Hanya muncul jika status masih diproses
                    ->action(fn($record) => $record->update(['status' => 'dikirim']))
                    ->requiresConfirmation(),
                Action::make('setStatuDibatalkan')
                    ->iconButton()
                    ->modalHeading('Konfirmasi Pembatalan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->hidden(fn($record) => $record->status !== 'diproses') // Hanya muncul jika status masih diproses
                    ->action(fn($record) => $record->update(['status' => 'dibatalkan']))
                    ->requiresConfirmation(),
                Action::make('setGagalKirim')
                    ->iconButton()
                    ->modalHeading('Konfirmasi Gagal Pengiriman')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->hidden(fn($record) => $record->status !== 'dikirim') // Hanya muncul jika status masih dikirim
                    ->action(fn($record) => $record->update(['status' => 'dibatalkan']))
                    ->requiresConfirmation(),
                Action::make('setStatuSelesai')
                    ->iconButton()
                    ->modalHeading('Konfirmasi Selesai')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->hidden(fn($record) => $record->status !== 'dikirim') // Hanya muncul jika status masih dikirim
                    ->action(fn($record) => $record->update(['status' => 'selesai']))
                    ->requiresConfirmation(),
                ViewAction::make()
                    ->iconButton()
                    ->modalHeading('Detail Transaksi')
                    ->modalDescription('Detail Transaksi Produk')
                    ->modalWidth('2xl')
                    ->slideOver(),
                EditAction::make()
                    ->iconButton()
                    ->modalHeading('Ubah Transaksi')
                    ->modalDescription('Ubah Transaksi Produk')
                    ->modalWidth('2xl')
                    ->slideOver(),
                DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('Hapus Transaksi'),
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
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTransactions::route('/'),
        ];
    }
}
