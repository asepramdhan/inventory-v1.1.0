<?php

namespace App\Filament\Resources\FinancialLogs\Pages;

use App\Filament\Exports\FinancialLogExporter;
use App\Filament\Resources\FinancialLogs\FinancialLogResource;
use App\Models\FinancialLog;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Auth;
use Override;

class ManageFinancialLogs extends ManageRecords
{
    protected static string $resource = FinancialLogResource::class;

    protected static ?string $title = 'Pencatatan Keuangan';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Catatan')
                ->modalHeading('Tambah Catatan Baru')
                ->modalDescription('Pastikan nama catatan belum terdaftar sebelumnya.')
                ->modalWidth('2xl')
                ->modalSubmitActionLabel('Tambah')
                ->createAnotherAction(fn(Action $action) => $action->label('Tambah & Buat Lagi'))
                // ->preserveFormDataWhenCreatingAnother(fn(array $data) => $data)
                ->icon('heroicon-o-plus-circle')
                ->slideOver(),

            ExportAction::make()
                ->exporter(FinancialLogExporter::class)
                ->label('Ekspor'),
        ];
    }

    #[Override]
    public function getTabs(): array
    {
        return [
            // 1. SEMUA CATATAN KEUANGAN
            'all' => Tab::make('Semua Catatan')
                ->icon('heroicon-o-bars-4')
                ->badge(static fn(): int => FinancialLog::query()->where('user_id', Auth::id())->count())
                ->badgeColor('gray'),

            // 2. PEMASUKAN (INCOME)
            'income' => Tab::make('Pemasukan')
                ->icon('heroicon-o-arrow-trending-up')
                ->badge(static fn(): int => FinancialLog::query()->where('user_id', Auth::id())->where('type', 'income')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn($query) => $query->where('type', 'income')),

            // 3. PENGELUARAN (EXPENSE)
            'expense' => Tab::make('Pengeluaran')
                ->icon('heroicon-o-arrow-trending-down')
                ->badge(static fn(): int => FinancialLog::query()->where('user_id', Auth::id())->where('type', 'expense')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn($query) => $query->where('type', 'expense')),

            // 4. TRANSAKSI YANG BELUM LUNAS (UTANG / PIUTANG TEMPO)
            'unpaid' => Tab::make('Belum Lunas')
                ->icon('heroicon-o-clock')
                ->badge(static fn(): int => FinancialLog::query()->where('user_id', Auth::id())->where('payment_status', 'unpaid')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn($query) => $query->where('payment_status', 'unpaid')),
        ];
    }

    #[Override]
    public function getDefaultActiveTab(): string|int|null
    {
        // Default langsung menampilkan Semua Catatan Keuangan
        return 'all';
    }
}
