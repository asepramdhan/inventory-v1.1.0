<?php

namespace App\Filament\Resources\AdsCosts\Widgets;

use App\Filament\Resources\AdsCosts\Pages\ManageAdsCosts;
use App\Models\AdsCost;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AdsCostWidget extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    protected static bool $isLazy = true;

    protected function getTablePage(): string
    {
        return ManageAdsCosts::class;
    }

    protected function getStats(): array
    {
        $userId = Auth::id();

        // 1. Data Semua Iklan (Sesuai filter tabel jika ada)
        $totalAllTime = $this->getPageTableQuery()->sum('amount');

        // 2. Data Iklan Hari Ini
        $totalToday = AdsCost::where('user_id', $userId)
            ->whereDate('created_at', now())
            ->sum('amount');

        // 3. Data Iklan Kemarin (untuk perbandingan persentase)
        $totalYesterday = AdsCost::where('user_id', $userId)
            ->whereDate('created_at', now()->subDay())
            ->sum('amount');

        // Hitung kenaikan/penurunan iklan hari ini vs kemarin
        $diff = $totalToday - $totalYesterday;
        $trendIcon = $diff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $trendColor = $diff > 0 ? 'danger' : 'success'; // Merah jika iklan naik, hijau jika turun

        // 4. Rata-rata Harian (30 hari terakhir)
        $avgDaily = AdsCost::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('amount') / 30;

        // 5. Data Chart (Trend 7 Hari Terakhir)
        $adTrend = collect(range(6, 0))->map(function ($days) use ($userId) {
            return AdsCost::where('user_id', $userId)
                ->whereDate('created_at', now()->subDays($days))
                ->sum('amount');
        })->toArray();

        return [
            Stat::make('Total Semua Iklan', 'Rp ' . number_format($totalAllTime, 0, ',', '.'))
                ->description('Total akumulasi biaya iklan')
                ->chart($adTrend)
                ->color('info'),

            Stat::make('Iklan Hari Ini', 'Rp ' . number_format($totalToday, 0, ',', '.'))
                ->description($diff >= 0 ? 'Naik Rp ' . number_format($diff, 0, ',', '.') : 'Turun Rp ' . number_format(abs($diff), 0, ',', '.'))
                ->descriptionIcon($trendIcon)
                ->color($trendColor),

            Stat::make('Rata-rata Harian', 'Rp ' . number_format($avgDaily, 0, ',', '.'))
                ->description('Berdasarkan data 30 hari terakhir')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),
        ];
    }
}
