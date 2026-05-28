<?php

namespace App\Filament\Resources\CapitalMutations;

use App\Filament\Resources\CapitalMutations\Pages\ManageCapitalMutations;
use App\Models\CapitalMutation;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

class CapitalMutationResource extends Resource
{
    protected static ?string $model = CapitalMutation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan & Analisa';

    protected static ?string $navigationLabel = ' Mutasi';

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::user()->id);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')->default(Auth::user()->id),
                // LANGSUNG INPUTAN UTAMA (Tanpa Section & Tanpa Grid Menyamping)
                Select::make('type')
                    ->label('Tipe Mutasi')
                    ->options([
                        'withdrawal' => 'Penarikan Saldo (Withdrawal)',
                        'supplier_payment' => 'Pembayaran Barang ke Produsen',
                    ])
                    ->required()
                    ->live()
                    ->native(false),

                DatePicker::make('date')
                    ->label('Tanggal Eksekusi')
                    ->default(now())
                    ->required()
                    ->extraAttributes(['class' => 'mt-4']), // Spacing agar tidak menempel

                Select::make('store_id')
                    ->label('Terikat dengan Toko?')
                    ->relationship(
                        'store',
                        'shop_name',
                        fn(Builder $query) => $query->where('user_id', Auth::user()->id)
                    )
                    ->placeholder('Pilih Toko (Opsional)')
                    ->searchable()
                    ->native(false)
                    ->extraAttributes(['class' => 'mt-4']),

                TextInput::make('amount')
                    ->label('Nominal (IDR)')
                    ->required()
                    ->autofocus()
                    ->prefix('Rp')
                    ->extraInputAttributes(['type' => 'text', 'inputmode' => 'numeric'])
                    ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))
                    ->dehydrateStateUsing(function ($state) {
                        if (! $state) return null;
                        return preg_replace('/[^0-9]/', '', $state);
                    })
                    ->extraAttributes(['class' => 'mt-4']),

                // KONDISI 1: Muncul HANYA KETIKA memilih Penarikan Saldo (withdrawal)
                TextInput::make('source')
                    ->label('Asal Saldo / Akun Toko')
                    ->placeholder('Contoh: Shopee MeowMeal.id, TikTok Shop, dll.')
                    ->required()
                    ->visible(fn(Get $get) => $get('type') === 'withdrawal')
                    ->extraAttributes(['class' => 'mt-4']),

                TextInput::make('destination')
                    ->label('Rekening Bank Tujuan')
                    ->placeholder('Contoh: Bank BCA, Mandiri Kas Inti, dll.')
                    ->required()
                    ->visible(fn(Get $get) => $get('type') === 'withdrawal')
                    ->extraAttributes(['class' => 'mt-4']),

                // KONDISI 2: Muncul HANYA KETIKA memilih Pembayaran Produsen (supplier_payment)
                TextInput::make('destination_supplier')
                    ->statePath('destination')
                    ->label('Nama Produsen / Suplier Tujuan')
                    ->placeholder('Contoh: Pabrik Hammock Bandung, Konveksi RR Sports')
                    ->required()
                    ->visible(fn(Get $get) => $get('type') === 'supplier_payment')
                    ->extraAttributes(['class' => 'mt-4']),

                TextInput::make('source_fund')
                    ->statePath('source')
                    ->label('Sumber Dana Pembayaran')
                    ->placeholder('Contoh: Kas BCA Bisnis, Cash Fisik')
                    ->required()
                    ->visible(fn(Get $get) => $get('type') === 'supplier_payment')
                    ->extraAttributes(['class' => 'mt-4']),

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
                    // ->visible(fn(Get $get) => $get('type') === 'supplier_payment')
                    ->extraAttributes(['class' => 'mt-4']),

                DatePicker::make('due_date')
                    ->label('Tanggal Jatuh Tempo')
                    ->required()
                    ->visible(fn(Get $get) => $get('payment_status') === 'unpaid')
                    ->extraAttributes(['class' => 'mt-4']),

                // BLOK DOKUMENTASI TAMBAHAN (Tetap Vertikal Kebawah)
                TextInput::make('reference_number')
                    ->label('Nomor Referensi / ID Transaksi Bank')
                    ->placeholder('Contoh: Ref-102930492, No. Nota Produsen')
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'mt-4']),

                Textarea::make('description')
                    ->label('Keterangan Tambahan')
                    ->placeholder('Tulis detail tambahan catatan mutasi jika diperlukan...')
                    ->rows(3)
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'mt-4']),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 2,
                    'lg' => 4,
                ])
                    ->columnSpanFull()
                    ->schema([
                        // Tipe Mutasi (Dibuat badge agar menarik)
                        TextEntry::make('type')
                            ->label('Tipe Mutasi')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'withdrawal' => 'success',
                                'supplier_payment' => 'warning',
                                default => 'gray',
                            })
                            ->columnSpan(2)
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'withdrawal' => 'Penarikan Saldo',
                                'supplier_payment' => 'Bayar Produsen',
                                default => ucfirst($state),
                            }),

                        TextEntry::make('created_at')
                            ->label('Tanggal Eksekusi')
                            ->date('d M y H:i')
                            ->columnSpan(2)
                            ->extraAttributes(['class' => 'mt-4']),

                        TextEntry::make('store.shop_name')
                            ->label('Terikat dengan Toko')
                            ->placeholder('Umum / Non-Toko')
                            ->columnSpan(2)
                            ->extraAttributes(['class' => 'mt-4']),

                        TextEntry::make('amount')
                            ->label('Nominal Transaksi')
                            ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                            ->weight('bold')
                            ->badge()
                            ->color('info')
                            ->columnSpan(2)
                            ->extraAttributes(['class' => 'mt-4']),

                        // DETAIL KONDISIONAL: Muncul hanya saat Penarikan Saldo (withdrawal)
                        TextEntry::make('source')
                            ->label('Asal Saldo / Akun Toko')
                            ->visible(fn($record) => $record?->type === 'withdrawal')
                            ->badge()
                            ->columnSpan(2)
                            ->extraAttributes(['class' => 'mt-4']),

                        TextEntry::make('destination')
                            ->label('Rekening Bank Tujuan')
                            ->visible(fn($record) => $record?->type === 'withdrawal')
                            ->badge()
                            ->color('info')
                            ->columnSpan(2)
                            ->extraAttributes(['class' => 'mt-4']),

                        TextEntry::make('payment_status')
                            ->label('Status Pembayaran')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'paid' => 'success',
                                'unpaid' => 'danger',
                                default => 'gray',
                            })
                            ->columnSpan(2)
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'paid' => 'Lunas',
                                'unpaid' => 'Tempo (Utang)',
                                default => ucfirst($state),
                            })
                            ->extraAttributes(['class' => 'mt-4']),

                        TextEntry::make('source')
                            ->label('Sumber Dana Pembayaran')
                            ->visible(fn($record) => $record?->type === 'supplier_payment')
                            ->badge()
                            ->columnSpan(2)
                            ->extraAttributes(['class' => 'mt-4']),

                        // DETAIL KONDISIONAL: Muncul hanya saat Bayar Produsen (supplier_payment)
                        TextEntry::make('destination')
                            ->label('Nama Produsen / Suplier Tujuan')
                            ->visible(fn($record) => $record?->type === 'supplier_payment')
                            ->columnSpan(2)
                            ->extraAttributes(['class' => 'mt-4']),

                        // DOKUMENTASI TAMBAHAN
                        TextEntry::make('reference_number')
                            ->label('Nomor Referensi / ID Transaksi')
                            ->placeholder('-')
                            ->columnSpan(2)
                            ->extraAttributes(['class' => 'mt-4']),

                        TextEntry::make('description')
                            ->label('Keterangan Tambahan')
                            ->placeholder('-')
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'mt-4']),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('CapitalMutation')
            ->filtersFormColumns(2)
            ->deferFilters(false)
            ->persistFiltersInSession(true)
            ->persistSortInSession(true)
            ->deferLoading()
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d M y H:i')
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

                // TextColumn::make('store.shop_name')
                //     ->label('Toko')
                //     ->placeholder('Non-Toko')
                //     ->searchable(),

                TextColumn::make('amount')
                    ->label('Nominal (IDR)')
                    ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                    ->sortable(),

                TextColumn::make('source')
                    ->label('Asal Dana')
                    ->placeholder('-'),

                TextColumn::make('destination')
                    ->label('Tujuan Dana')
                    ->placeholder('-'),

                TextColumn::make('payment_status')
                    ->label('Status')
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

                // TextColumn::make('due_date')
                //     ->label('Jatuh Tempo')
                //     ->date('d-m-Y')
                //     ->placeholder('-')
                //     ->toggleable(isToggledHiddenByDefault: true),

                // TextColumn::make('reference_number')
                //     ->label('No. Ref / Nota')
                //     ->placeholder('-')
                //     ->toggleable(isToggledHiddenByDefault: true)
                //     ->searchable(),
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
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->modalHeading('Detail Mutasi')
                    ->modalDescription('Detail Mutasi Pencatatan Keuangan')
                    ->modalWidth('xl')
                    ->slideOver(),
                EditAction::make()
                    ->iconButton()
                    ->modalHeading('Ubah Mutasi')
                    ->modalDescription('Ubah Mutasi Pencatatan Keuangan')
                    ->modalWidth('2xl')
                    ->slideOver(),
                DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('Hapus Mutasi'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Hapus Mutasi Yang Dipilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCapitalMutations::route('/'),
        ];
    }
}
