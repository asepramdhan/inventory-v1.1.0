<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\LatestTransactions;
use App\Filament\Widgets\MarginAnalysisWidget;
use App\Models\Store;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
// use Illuminate\Contracts\Support\Htmlable;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
// use Override;
use UnitEnum;

class MarginAnalysis extends Page
{
    use HasFiltersForm;

    protected string $view = 'filament.pages.margin-analysis';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan & Analisa';

    protected static ?string $navigationLabel = 'Analisa Margin'; // Judul di Sidebar

    protected static ?string $title = 'Analisa Margin'; // Judul di Halaman

    // #[Override]
    // public function getSubheading(): string|Htmlable|null
    // {
    //     return 'Analisa Margin Keuntungan';
    // }

    // Ubah type hint menjadi Schema sesuai pesan error
    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([ // Gunakan components() jika Schema, bukan schema()
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Dari Tanggal')
                            ->default(now()->startOfMonth())
                            ->live(),
                        DatePicker::make('endDate')
                            ->label('Sampai Tanggal')
                            ->default(now())
                            ->live(),
                        Select::make('storeId') // Ini kunci filternya
                            ->label('Pilih Toko')
                            ->placeholder('Semua Toko')
                            ->options(Store::where('user_id', Auth::user()->id)
                                ->pluck('shop_name', 'id')) // Ambil nama toko dari database
                            ->searchable()
                            ->preload()
                            ->live(), // Penting agar widget langsung update
                    ])
                    ->columns(3) // Ubah ke 3 agar sebaris penuh
                    ->columnSpanFull()
                    ->compact(),
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MarginAnalysisWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            LatestTransactions::class,
        ];
    }
}
