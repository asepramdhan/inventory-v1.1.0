<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class TransactionForm
{
    /**
     * Fungsi Helper Terpusat untuk update total harga secara real-time lintas komponen
     */
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

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // BUNGKUS UTAMA: Membagi halaman menjadi 2 kolom (Kiri: Informasi, Kanan: Detail Produk)
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([

                        // Kiri: Informasi Transaksi
                        Section::make('Informasi Transaksi')
                            ->description('Detail toko, nomor pesanan, dan status saat ini.')
                            ->compact()
                            ->columns(1) // Dipaksa 1 kolom vertikal ke bawah supaya tidak berdesakan ke samping
                            ->columnSpan(1)
                            ->components([
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
                            ]),

                        // Kanan: Detail Produk yang Dibeli
                        Section::make('Detail Produk yang Dibeli')
                            ->description('Pilih item produk dan tentukan kuantitasnya.')
                            ->compact()
                            ->columnSpan(1)
                            ->components([
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
                                                'onclick' => "setTimeout(() => { window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' }); }, 200)",
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
                                            ->native(false)
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                $product = Product::find($state);
                                                if ($product) {
                                                    $set('price', $product->price);
                                                }
                                                self::updateTotalPrice($set, $get);
                                            })
                                            ->columnSpan(3),

                                        TextInput::make('price')
                                            ->label('Harga')
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
                                            ->columnSpan(2),

                                        TextInput::make('quantity')
                                            ->label('Qty')
                                            ->required()
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->live()
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                self::updateTotalPrice($set, $get);
                                            })
                                            ->columnSpan(1),
                                    ])
                                    ->columnSpanFull()
                                    ->columns(6)
                                    ->defaultItems(1)
                                    ->addActionAlignment(Alignment::Start)
                                    ->collapsible()
                                    ->cloneable()
                                    ->itemNumbers(),
                            ]),
                    ]),

                // 3. Area Grand Total Price Box (Melebar penuh di bawah kedua kolom)
                Grid::make(3)
                    ->columnSpanFull()
                    ->components([
                        Hidden::make('total_price')->default(0),

                        Placeholder::make('total_price_display')
                            ->label('')
                            ->columnStart(3) // Ditaruh di pojok paling kanan bawah
                            ->content(function (callable $get) {
                                $totalPrice = $get('total_price') ?? 0;
                                $totalFormatted = number_format($totalPrice, 0, ',', '.');

                                return new HtmlString("
                                    <div class='flex flex-col items-end justify-center p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700/50 mt-4'>
                                        <span class='text-xs font-semibold text-gray-400 uppercase tracking-wider'>Grand Total</span>
                                        <span class='text-3xl font-black text-primary-600 dark:text-primary-400 tracking-tight mt-1'>Rp $totalFormatted</span>
                                    </div>
                                ");
                            }),
                    ]),
            ]);
    }
}
