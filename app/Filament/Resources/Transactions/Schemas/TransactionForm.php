<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Product;
use App\Models\Store;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class TransactionForm
{
    /**
     * Fungsi Helper untuk menghitung total harga secara realtime lintas komponen
     */
    public static function updateTotalPrice($component)
    {
        // Mengambil instance Livewire utama tempat form berada
        $livewire = $component->getLivewire();

        // Mengambil seluruh data item yang ada di dalam repeater saat ini
        $items = $livewire->data['items'] ?? [];

        $total = 0;

        // Looping untuk menjumlahkan (Harga * Jumlah) dari setiap baris produk
        foreach ($items as $item) {
            $price = floatval($item['price'] ?? 0);
            $quantity = intval($item['quantity'] ?? 1);
            $total += $price * $quantity;
        }

        // Memasukkan hasil penjumlahan langsung ke state 'total_price' di form utama
        $livewire->data['total_price'] = $total;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. Data Utama Transaksi (Header)
                Section::make()
                    ->schema([
                        Select::make('store_id')
                            ->label('Toko')
                            ->options(
                                Store::query()
                                    ->where('user_id', Auth::user()->id)
                                    ->pluck('shop_name', 'id')
                            )
                            ->columnSpan(1)
                            ->searchable()
                            ->required(),

                        TextInput::make('order_number')
                            ->label('Nomor Pesanan')
                            ->placeholder('Contoh: ORD-2024001 atau No. Resi')
                            ->columnSpan(1),

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
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
                    ->compact(),

                // 2. Data Dinamis Banyak Produk (Repeater menggunakan Relasi)
                Repeater::make('items')
                    ->relationship('items')
                    ->label('Daftar Produk Yang Dibeli')
                    ->live()
                    // Memicu hitung total ketika ada baris yang ditambah atau dihapus dari repeater
                    ->afterStateUpdated(function ($component) {
                        self::updateTotalPrice($component);
                    })
                    // TAMBAHKAN INI: Custom Action untuk Scroll Otomatis
                    ->addAction(function (Action $action) {
                        return $action
                            ->label('Tambah Daftar Produk')
                            ->extraAttributes([
                                // Script JS untuk scroll ke paling bawah halaman
                                'onclick' => "setTimeout(() => { window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' }); }, 200)",
                            ]);
                    })
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->options(
                                Product::query()
                                    ->where('user_id', Auth::user()->id)
                                    ->where('status', true)
                                    ->pluck('sku', 'id')
                            )
                            ->searchable()
                            ->required()
                            ->live()
                            // ->afterStateUpdated(function ($state, Set $set, Get $get) {
                            //     $product = Product::find($state);
                            //     $price = $product->price ?? 0;
                            //     $set('price', $price);
                            //     $qty = $get('quantity') ?? 1;
                            //     $set('quantity', $qty);

                            //     $set('../../total_price', $price * $qty);
                            // })
                            ->afterStateUpdated(function ($state, callable $set, $component) {
                                $product = Product::find($state);
                                if ($product) {
                                    $set('price', $product->price);
                                }
                                // Memicu hitung total saat produk dipilih/diubah
                                self::updateTotalPrice($component);
                            })
                            ->columnSpan(3),

                        TextInput::make('price')
                            ->label('Harga Jual')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->live()
                            // Memicu hitung total saat harga satuan diubah manual
                            ->afterStateUpdated(function ($component) {
                                self::updateTotalPrice($component);
                            })
                            ->columnSpan(2),

                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->live()
                            // ->afterStateUpdated(function ($state, Set $set, Get $get) {
                            //     $price = $get('price') ?? 0;
                            //     $set('../../total_price', $price * $state);
                            // })
                            // ->live(debounce: 500)
                            // // Memicu hitung total saat jumlah/quantity diubah
                            ->afterStateUpdated(function ($component) {
                                self::updateTotalPrice($component);
                            })
                            ->columnSpan(1),
                    ])
                    ->columnSpanFull()
                    ->columns(6)
                    ->defaultItems(1)
                    ->addActionAlignment(Alignment::End)
                    ->collapsible()
                    ->cloneable()
                    ->itemNumbers()
                    ->addActionLabel('Tambah Daftar Produk'),

                Grid::make(1) // Membuat grid 3 kolom untuk mendorong total ke kanan
                    ->schema([
                        // Hidden field tetap ada agar data tersimpan ke DB
                        Hidden::make('total_price')->default(0),

                        // Placeholder hanya untuk tampilan (Visual saja)
                        Placeholder::make('total_price_display')
                            ->label('Total Harga')
                            ->columnStart(1) // Mulai di kolom ke-3 (paling kanan)
                            ->content(function ($get) {
                                $total = number_format($get('total_price'), 0, ',', '.');
                                return new HtmlString("
                                    <div class='flex flex-col items-end'>
                                        <span class='text-sm text-gray-500'>Grand Total:</span>
                                        <span class='text-2xl font-bold text-primary-600'>Rp $total</span>
                                    </div>
                                ");
                            }),
                    ]),
            ]);
    }
}
