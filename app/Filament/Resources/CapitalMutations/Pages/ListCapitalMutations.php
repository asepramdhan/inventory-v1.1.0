<?php

namespace App\Filament\Resources\CapitalMutations\Pages;

use App\Filament\Resources\CapitalMutations\CapitalMutationResource;
use App\Models\CapitalMutation;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Override;

class ListCapitalMutations extends ListRecords
{
    protected static string $resource = CapitalMutationResource::class;

    protected static ?string $title = 'Mutasi Keuangan';

    #[Override]
    public function getSubheading(): string|Htmlable|null
    {
        return 'Kelola mutasi pembayaran dan penarikan';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Mutasi Baru'),
        ];
    }

    #[Override]
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Mutasi')
                ->icon('heroicon-o-bars-4')
                ->badge(static fn(): int => CapitalMutation::query()->where('user_id', Auth::id())->count())
                ->badgeColor('primary')
                ->deferBadge(),

            'withdrawal' => Tab::make('Penarikan Saldo')
                ->icon('heroicon-o-arrow-down-tray')
                ->badge(static fn(): int => CapitalMutation::query()->where('user_id', Auth::id())->where('type', 'withdrawal')->count())
                ->badgeColor('success')
                ->deferBadge()
                ->modifyQueryUsing(fn($query) => $query->where('type', 'withdrawal')),

            'supplier_payment' => Tab::make('Pembayaran Produsen')
                ->icon('heroicon-o-credit-card')
                ->badge(static fn(): int => CapitalMutation::query()->where('user_id', Auth::id())->where('type', 'supplier_payment')->count())
                ->badgeColor('warning')
                ->deferBadge()
                ->modifyQueryUsing(fn($query) => $query->where('type', 'supplier_payment')),

            'unpaid_tempo' => Tab::make('Utang / Tempo')
                ->icon('heroicon-o-exclamation-circle')
                ->badge(static fn(): int => CapitalMutation::query()->where('user_id', Auth::id())->where('type', 'supplier_payment')->where('payment_status', 'unpaid')->count())
                ->badgeColor('danger')
                ->deferBadge()
                ->modifyQueryUsing(fn($query) => $query->where('type', 'supplier_payment')->where('payment_status', 'unpaid')),
        ];
    }

    #[Override]
    public function getDefaultActiveTab(): string|int|null
    {
        // Default awal diarahkan ke Semua Mutasi, atau bisa ganti 'unpaid_tempo' jika ingin fokus ke utang dulu
        return 'all';
    }
}
