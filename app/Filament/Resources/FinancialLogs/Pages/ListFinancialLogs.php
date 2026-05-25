<?php

namespace App\Filament\Resources\FinancialLogs\Pages;

use App\Filament\Resources\FinancialLogs\FinancialLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Override;

class ListFinancialLogs extends ListRecords
{
    protected static string $resource = FinancialLogResource::class;

    protected static ?string $title = 'Pencatatan Keuangan'; // Judul di Halaman

    #[Override]
    public function getSubheading(): string|Htmlable|null
    {
        return 'Kelola pencatatan keuangan';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Catatan'),
        ];
    }
}
