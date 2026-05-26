<?php

namespace App\Filament\Resources\CapitalMutations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class CapitalMutationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama Mutasi')
                    ->description('Tentukan tipe mutasi modal dan detail dasarnya di sini.')
                    ->columns(2)
                    ->components([

                        Select::make('type')
                            ->label('Tipe Mutasi')
                            ->options([
                                'withdrawal' => 'Penarikan Saldo (Withdrawal)',
                                'supplier_payment' => 'Pembayaran Barang ke Produsen',
                            ])
                            ->required()
                            ->live() // Memicu form agar langsung merespon perubahan pilihan
                            ->native(false),

                        DatePicker::make('date')
                            ->label('Tanggal Eksekusi')
                            ->default(now())
                            ->required(),

                        Select::make('store_id')
                            ->label('Terikat dengan Toko?')
                            ->relationship('store', 'shop_name')
                            ->placeholder('Pilih Toko (Opsional)')
                            ->searchable()
                            ->native(false),

                        TextInput::make('amount')
                            ->label('Nominal (IDR)')
                            ->required()
                            ->autofocus()
                            ->prefix('Rp')
                            ->extraInputAttributes(['type' => 'text', 'inputmode' => 'numeric'])
                            ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))

                            // PERBAIKAN: Buang koma (,), atau bersihkan semua karakter non-digit agar aman 100%
                            ->dehydrateStateUsing(function ($state) {
                                if (! $state) return null;

                                // Menghapus semua karakter selain angka murni (termasuk titik atau koma)
                                return preg_replace('/[^0-9]/', '', $state);
                            }),
                    ]),

                Section::make('Detail Aliran Dana & Status')
                    ->columns(2)
                    ->components([

                        // KONDISI 1: Muncul HANYA KETIKA memilih Penarikan Saldo (withdrawal)
                        TextInput::make('source')
                            ->label('Asal Saldo / Akun Toko')
                            ->placeholder('Contoh: Shopee MeowMeal.id, TikTok Shop, dll.')
                            ->required()
                            ->visible(fn(Get $get) => $get('type') === 'withdrawal'),

                        TextInput::make('destination')
                            ->label('Rekening Bank Tujuan')
                            ->placeholder('Contoh: Bank BCA, Mandiri Kas Inti, dll.')
                            ->required()
                            ->visible(fn(Get $get) => $get('type') === 'withdrawal'),


                        // KONDISI 2: Muncul HANYA KETIKA memilih Pembayaran Produsen (supplier_payment)
                        TextInput::make('destination_supplier') // Kita petakan ke 'destination' di database lewat state mapping nanti
                            ->statePath('destination')
                            ->label('Nama Produsen / Suplier Tujuan')
                            ->placeholder('Contoh: Pabrik Hammock Bandung, Konveksi RR Sports')
                            ->required()
                            ->visible(fn(Get $get) => $get('type') === 'supplier_payment')
                            ->columnSpanFull(),

                        TextInput::make('source_fund') // Kita petakan ke 'source' di database
                            ->statePath('source')
                            ->label('Sumber Dana Pembayaran')
                            ->placeholder('Contoh: Kas BCA Bisnis, Cash Fisik')
                            ->required()
                            ->visible(fn(Get $get) => $get('type') === 'supplier_payment'),

                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'paid' => 'Lunas',
                                'unpaid' => 'Belum Lunas (Tempo / Utang)',
                            ])
                            ->required()
                            ->default('paid')
                            ->live()
                            ->native(false)
                            ->visible(fn(Get $get) => $get('type') === 'supplier_payment'),

                        DatePicker::make('due_date')
                            ->label('Tanggal Jatuh Tempo')
                            ->required()
                            ->visible(fn(Get $get) => $get('type') === 'supplier_payment' && $get('payment_status') === 'unpaid'),
                    ]),

                Section::make('Dokumentasi Tambahan')
                    ->columns(2)
                    ->components([
                        TextInput::make('reference_number')
                            ->label('Nomor Referensi / ID Transaksi Bank')
                            ->placeholder('Contoh: Ref-102930492, No. Nota Produsen')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Keterangan Tambahan')
                            ->placeholder('Tulis detail tambahan catatan mutasi jika diperlukan...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
