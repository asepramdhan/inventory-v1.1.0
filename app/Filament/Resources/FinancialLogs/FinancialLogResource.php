<?php

namespace App\Filament\Resources\FinancialLogs;

use App\Filament\Resources\FinancialLogs\Pages\ManageFinancialLogs;
use App\Models\FinancialLog;
use App\Models\Store;
use BackedEnum;
use Filament\Actions\Action;
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

class FinancialLogResource extends Resource
{
    protected static ?string $model = FinancialLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan & Analisa';

    protected static ?string $recordTitleAttribute = 'FinancialLog';

    protected static ?string $navigationLabel = ' Keuangan';

    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::user()->id);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')->default(Auth::user()->id),

                // LANGSUNG INPUT UTAMA (Tanpa Section & Mengalir Vertikal)
                Select::make('store_id')
                    ->label('Toko / Store')
                    ->options(
                        Store::query()
                            ->where('user_id', Auth::user()->id)
                            ->pluck('shop_name', 'id')
                    )
                    ->placeholder('Pilih Toko')
                    ->searchable()
                    ->native(false)
                    ->required(),

                DatePicker::make('date')
                    ->label('Tanggal Pencatatan')
                    ->default(now())
                    ->required()
                    ->extraAttributes(['class' => 'mt-4']),

                Select::make('type')
                    ->label('Tipe Keuangan')
                    ->options([
                        'income' => 'Pemasukan (Income)',
                        'expense' => 'Pengeluaran (Expense)',
                    ])
                    ->default('expense')
                    ->native(false)
                    ->live()
                    ->required()
                    ->extraAttributes(['class' => 'mt-4']),

                Select::make('category')
                    ->label('Kategori')
                    ->options([
                        'Stock' => 'Pemasukan Barang (Stock)',
                        'Ads' => 'Biaya Iklan (Ads Spend)',
                        'Operational' => 'Operasional',
                        'Shipping' => 'Biaya Pengiriman',
                        'Lainnya' => 'Lain-lain',
                    ])
                    ->native(false)
                    ->live()
                    ->required()
                    ->extraAttributes(['class' => 'mt-4']),

                Select::make('platform')
                    ->label('Platform Marketplace')
                    ->options([
                        'shopee' => 'Shopee',
                        'lazada' => 'Lazada',
                        'tokopedia' => 'Tokopedia',
                        'tiktokshop' => 'Tiktok Shop',
                        'offline' => 'Offline / Luar Marketplace',
                    ])
                    ->native(false)
                    ->nullable()
                    ->extraAttributes(['class' => 'mt-4']),

                TextInput::make('amount')
                    ->label('Nominal (Rupiah)')
                    ->prefix('Rp')
                    ->required()
                    ->extraInputAttributes(['type' => 'text', 'inputmode' => 'numeric'])
                    ->mask(RawJs::make('$money($input, \'.\', \',\', 0)'))
                    ->dehydrateStateUsing(function ($state) {
                        if (! $state) return null;
                        return preg_replace('/[^0-9]/', '', $state);
                    })
                    ->extraAttributes(['class' => 'mt-4']),

                // HANYA MUNCUL JIKA TIPE NYA ADALAH 'EXPENSE' (PENGELUARAN)
                Select::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => 'Cash / Langsung Lunas',
                        'weekly_term' => 'Tempo Mingguan',
                        'monthly_term' => 'Tempo Bulanan',
                    ])
                    ->default('cash')
                    ->native(false)
                    ->live()
                    ->visible(fn(Get $get) => $get('type') === 'expense')
                    ->required(fn(Get $get) => $get('type') === 'expense')
                    ->extraAttributes(['class' => 'mt-4']),

                Select::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'paid' => 'Lunas (Paid)',
                        'unpaid' => 'Belum Dibayar (Unpaid / Utang)',
                    ])
                    ->default('paid')
                    ->native(false)
                    ->visible(fn(Get $get) => $get('type') === 'expense')
                    ->required(fn(Get $get) => $get('type') === 'expense')
                    ->extraAttributes(['class' => 'mt-4']),

                DatePicker::make('due_date')
                    ->label('Tanggal Jatuh Tempo')
                    ->visible(fn(Get $get) => $get('type') === 'expense' && in_array($get('payment_method'), ['weekly_term', 'monthly_term']))
                    ->required(fn(Get $get) => $get('type') === 'expense' && in_array($get('payment_method'), ['weekly_term', 'monthly_term']))
                    ->extraAttributes(['class' => 'mt-4']),

                Textarea::make('description')
                    ->label('Catatan Tambahan')
                    ->placeholder('Contoh: Sisa pembayaran supplier baju koko 50pcs')
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
                    'lg' => 6,
                ])
                    ->columnSpanFull()
                    ->schema([
                        // Tipe Keuangan (Badge)
                        TextEntry::make('type')
                            ->label('Tipe Keuangan')
                            ->columnSpan(3)
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'income' => 'success',
                                'expense' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'income' => 'Pemasukan (Income)',
                                'expense' => 'Pengeluaran (Expense)',
                                default => ucfirst($state),
                            }),

                        TextEntry::make('created_at')
                            ->label('Tanggal Pencatatan')
                            ->columnSpan(3)
                            ->date('d M Y H:i')
                            ->extraAttributes(['class' => 'mt-4']),

                        TextEntry::make('store.shop_name')
                            ->label('Toko / Store')
                            ->columnSpan(2)
                            ->placeholder('Umum / Non-Toko')
                            ->extraAttributes(['class' => 'mt-4']),

                        TextEntry::make('category')
                            ->label('Kategori')
                            ->columnSpan(2)
                            ->extraAttributes(['class' => 'mt-4']),

                        TextEntry::make('platform')
                            ->label('Platform Marketplace')
                            ->columnSpan(2)
                            ->placeholder('Luar Marketplace (Offline)')
                            ->formatStateUsing(fn(string $state): string => ucfirst($state))
                            ->extraAttributes(['class' => 'mt-4']),

                        TextEntry::make('amount')
                            ->label('Nominal Transaksi')
                            ->columnSpan(3)
                            ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                            ->weight('bold')
                            ->extraAttributes(['class' => 'mt-4']),

                        // KONDISIONAL DETAIL: Hanya muncul jika tipe transaksi adalah 'expense'
                        TextEntry::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->columnSpan(3)
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'cash' => 'Cash / Langsung Lunas',
                                'weekly_term' => 'Tempo Mingguan',
                                'monthly_term' => 'Tempo Bulanan',
                                default => ucfirst($state),
                            })
                            ->visible(fn($record) => $record?->type === 'expense')
                            ->extraAttributes(['class' => 'mt-4']),

                        TextEntry::make('payment_status')
                            ->label('Status Pembayaran')
                            ->columnSpan(3)
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'paid' => 'success',
                                'unpaid' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'paid' => 'Lunas (Paid)',
                                'unpaid' => 'Belum Dibayar (Utang)',
                                default => ucfirst($state),
                            })
                            ->visible(fn($record) => $record?->type === 'expense')
                            ->extraAttributes(['class' => 'mt-4']),

                        TextEntry::make('due_date')
                            ->label('Tanggal Jatuh Tempo')
                            ->columnSpan(3)
                            ->date('d F Y')
                            ->placeholder('-')
                            ->visible(fn($record) => $record?->type === 'expense' && $record?->payment_status === 'unpaid')
                            ->extraAttributes(['class' => 'mt-4']),

                        // CATATAN TAMBAHAN
                        TextEntry::make('description')
                            ->label('Catatan Tambahan')
                            ->columnSpanFull()
                            ->placeholder('-')
                            ->extraAttributes(['class' => 'mt-4']),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('FinancialLog')
            ->deferFilters(false)
            ->persistFiltersInSession(true)
            ->deferLoading()
            ->columns([
                // 1. GABUNGAN TANGGAL & TOKO
                TextColumn::make('created_at')
                    ->label('Tanggal / Toko')
                    ->date('d M Y H:i')
                    ->description(fn($record) => "Toko: " . ($record->store?->shop_name ?? '-'))
                    ->searchable()
                    ->sortable(),

                // 2. GABUNGAN TIPE & KATEGORI
                TextColumn::make('type')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'income' => 'success',
                        'expense' => 'danger',
                    })
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->searchable()
                    ->description(fn($record) => "Jenis: {$record->category}"),

                // 3. GABUNGAN NOMINAL & STATUS/METODE PEMBAYARAN
                TextColumn::make('amount')
                    ->label('Nominal / Status')
                    ->money('IDR', locale: 'id_ID', decimalPlaces: 0)
                    ->weight('bold')
                    ->description(function ($record) {
                        $method = match ($record->payment_method) {
                            'cash' => 'Cash',
                            'weekly_term' => 'Tempo Mingguan',
                            'monthly_term' => 'Tempo Bulanan',
                        };

                        $status = $record->payment_status === 'paid'
                            ? '<span style="color: #22c55e;">● Lunas</span>' // Warna hijau
                            : '<span style="color: #ef4444;">● Belum Lunas</span>'; // Warna merah

                        if ($record->payment_status === 'unpaid' && $record->due_date) {
                            $dueDate = \Carbon\Carbon::parse($record->due_date)->format('d M Y');
                            $status .= "<br><span style='font-size: 0.75rem; color: #9ca3af;'>(Jatuh Tempo: {$dueDate})</span>";
                        }

                        // Kita bungkus dengan HtmlString agar tag <br> dan inline CSS di dalam description dieksekusi
                        return new \Illuminate\Support\HtmlString("{$method} <br> {$status}");
                    })
                    ->sortable(),

                // 4. KETERANGAN (Tampilan UI Dipercantik & Rapis)
                // TextColumn::make('description')
                //     ->label('Keterangan')
                //     ->searchable()
                //     ->html()
                //     ->formatStateUsing(function (?string $state) {
                //         if (! $state) return '-';

                //         // Pecah teks berdasarkan Enter (\n)
                //         $lines = explode("\n", str_replace("\r", "", $state));

                //         // Bersihkan spasi kosong
                //         $filteredLines = array_values(array_filter(array_map('trim', $lines)));

                //         if (empty($filteredLines)) return '-';

                //         $totalLines = count($filteredLines);
                //         $maxVisible = 2; // Batas baris langsung terlihat

                //         // Format baris teks dengan bulatan ● yang rapi
                //         $buildLinesHtml = function ($items) {
                //             $html = '';
                //             foreach ($items as $line) {
                //                 $html .= "<div class='flex items-start gap-1.5 py-0.5'>
                //                             <span style='color: #9ca3af; font-size: 0.75rem; flex-shrink: 0; margin-top: 2px;'>●</span>
                //                             <span class='text-gray-600 dark:text-gray-300 line-clamp-2'> " . e($line) . "</span>
                //                         </div>";
                //             }
                //             return $html;
                //         };

                //         // Jika 2 baris atau kurang, tampilkan langsung
                //         if ($totalLines <= $maxVisible) {
                //             return new \Illuminate\Support\HtmlString(
                //                 "<div style='max-width: 280px;' class='text-xs space-y-0.5'>
                //                     " . $buildLinesHtml($filteredLines) . "
                //                 </div>"
                //             );
                //         }

                //         // Jika lebih dari 2 baris, bagi data
                //         $visibleItems = $buildLinesHtml(array_slice($filteredLines, 0, $maxVisible));
                //         $collapsedItems = $buildLinesHtml(array_slice($filteredLines, $maxVisible));

                //         // Tampilan dengan Badge Button Collapse yang modern
                //         return new \Illuminate\Support\HtmlString(
                //             "<div style='max-width: 280px;' class='text-xs space-y-1'>
                //                 <div>{$visibleItems}</div>

                //                 <details class='group focus:outline-none' onclick='event.stopPropagation();'>
                //                     <summary class='inline-flex items-center gap-1 cursor-pointer rounded-md bg-gray-50 dark:bg-gray-800 px-2 py-1 text-[11px] font-medium text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition list-none focus:outline-none' style='list-style: none;'>
                //                         <span class='group-open:hidden'>+ Selengkapnya (" . ($totalLines - $maxVisible) . ")</span>
                //                     </summary>
                //                     <div class='mt-1.5 pl-1 border-l border-dashed border-gray-200 dark:border-gray-700 space-y-0.5'>
                //                         {$collapsedItems}
                //                     </div>
                //                 </details>
                //             </div>"
                //         );
                //     }),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('Status Bayar')
                    ->options([
                        'paid' => 'Lunas',
                        'unpaid' => 'Belum Lunas (Utang)',
                    ]),
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'Stock' => 'Pemasukan Barang',
                        'Ads' => 'Iklan',
                    ]),
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                Action::make('markAsPaid')
                    ->label('Set Lunas')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->button() // Ubah jadi bentuk button kecil biar mencolok untuk eksekusi
                    ->size('sm')
                    ->visible(fn($record) => $record->payment_status === 'unpaid')
                    ->action(fn($record) => $record->update(['payment_status' => 'paid'])),
                ViewAction::make()
                    ->iconButton()
                    ->modalHeading('Detail Keuangan')
                    ->modalDescription('Detail Pencatatan Keuangan')
                    ->modalWidth('xl')
                    ->slideOver(),
                EditAction::make()
                    ->iconButton()
                    ->modalHeading('Ubah Keuangan')
                    ->modalDescription('Ubah Pencatatan Keuangan')
                    ->modalWidth('2xl')
                    ->slideOver(),
                DeleteAction::make()
                    ->iconButton()
                    ->modalHeading('Hapus Keuangan'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Hapus Keuangan Yang Dipilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFinancialLogs::route('/'),
        ];
    }
}
