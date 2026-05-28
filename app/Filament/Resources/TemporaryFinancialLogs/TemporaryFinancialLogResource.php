<?php

namespace App\Filament\Resources\TemporaryFinancialLogs;

use App\Filament\Resources\TemporaryFinancialLogs\Pages\ManageTemporaryFinancialLogs;
use App\Models\FinancialLog;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

class TemporaryFinancialLogResource extends Resource
{
    protected static ?string $model = FinancialLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan & Analisa';

    protected static ?string $recordTitleAttribute = 'FinancialLog';

    protected static ?string $navigationLabel = 'Temporary Keuangan';

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
                TextInput::make('FinancialLog')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('FinancialLog'),
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
            'index' => ManageTemporaryFinancialLogs::route('/'),
        ];
    }
}
