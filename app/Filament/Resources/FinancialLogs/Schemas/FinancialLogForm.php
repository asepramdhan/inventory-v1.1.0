<?php

namespace App\Filament\Resources\FinancialLogs\Schemas;

use App\Models\Store;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;

class FinancialLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->columns(2)
                    ->schema([
                        Hidden::make('user_id')->default(Auth::user()->id),

                        Select::make('store_id')
                            ->label('Toko / Store')
                            ->options(
                                Store::query()
                                    ->where('user_id', Auth::user()->id)
                                    ->pluck('shop_name', 'id')
                            )
                            ->nullable() // Buat nullable jika ada pengeluaran non-toko
                            ->required(),

                        DatePicker::make('date')
                            ->label('Tanggal Pencatatan')
                            ->default(now())
                            ->required(),

                        Select::make('type')
                            ->label('Tipe Keuangan')
                            ->options([
                                'income' => 'Pemasukan (Income)',
                                'expense' => 'Pengeluaran (Expense)',
                            ])
                            ->default('expense')
                            ->live() // Memicu perubahan form secara real-time
                            ->required(),

                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'Stock' => 'Pemasukan Barang (Stock)',
                                'Ads' => 'Biaya Iklan (Ads Spend)',
                                'Operational' => 'Operasional',
                                'Shipping' => 'Biaya Pengiriman',
                                'Lainnya' => 'Lain-lain',
                            ])
                            ->live()
                            ->required(),

                        Select::make('platform')
                            ->label('Platform Marketplace')
                            ->options([
                                'shopee' => 'Shopee',
                                'lazada' => 'Lazada',
                                'tokopedia' => 'Tokopedia',
                                'tiktokshop' => 'Tiktok Shop',
                                'offline' => 'Offline / Luar Marketplace',
                            ])
                            ->nullable(),
                    ]),

                Section::make('Detail Pembayaran & Transaksi')
                    ->columns(2)
                    ->schema([
                        TextInput::make('amount')
                            ->label('Nominal (Rupiah)')
                            ->prefix('Rp')
                            ->required()
                            ->extraInputAttributes(['type' => 'text', 'inputmode' => 'numeric'])
                            ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))

                            // PERBAIKAN: Buang koma (,), atau bersihkan semua karakter non-digit agar aman 100%
                            ->dehydrateStateUsing(function ($state) {
                                if (! $state) return null;

                                // Menghapus semua karakter selain angka murni (termasuk titik atau koma)
                                return preg_replace('/[^0-9]/', '', $state);
                            }),

                        // Tampilkan pilihan termin hanya jika tipenya 'expense' dan kategorinya 'Stock'
                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'cash' => 'Cash / Langsung Lunas',
                                'weekly_term' => 'Tempo Mingguan',
                                'monthly_term' => 'Tempo Bulanan',
                            ])
                            ->default('cash')
                            ->live()
                            ->visible(fn(Get $get) => $get('type') === 'expense')
                            ->required(),

                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'paid' => 'Lunas (Paid)',
                                'unpaid' => 'Belum Dibayar (Unpaid / Utang)',
                            ])
                            ->default('paid')
                            ->visible(fn(Get $get) => $get('type') === 'expense')
                            ->required(),

                        DatePicker::make('due_date')
                            ->label('Tanggal Jatuh Tempo')
                            ->visible(fn(Get $get) => in_array($get('payment_method'), ['weekly_term', 'monthly_term']))
                            ->required(fn(Get $get) => in_array($get('payment_method'), ['weekly_term', 'monthly_term'])),

                        Textarea::make('description')
                            ->label('Catatan Tambahan')
                            ->placeholder('Contoh: Sisa pembayaran supplier baju koko 50pcs')
                            ->autofocus()
                            ->columnSpanFull(),
                    ])
            ]);
    }
}
