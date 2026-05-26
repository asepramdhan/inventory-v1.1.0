<?php

namespace App\Filament\Widgets;

use App\Models\AdsCost;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Override;

class MarginAnalysisWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;
    protected static bool $isLazy = true;

    #[Override]
    public function getColumns(): int|array|null
    {
        return 3;
    }

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $storeId = $this->filters['storeId'] ?? null;
        $userId = Auth::id();

        $chartEndDate = $endDate ? Carbon::parse($endDate) : now();
        $period = collect(range(6, 0))->map(fn($days) => (clone $chartEndDate)->subDays($days)->format('Y-m-d'));

        // 1. Total Biaya Iklan
        $totalAdsCost = AdsCost::query()
            ->where('user_id', $userId)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->sum('amount');

        // 2. Data Item (Omset, HPP, Admin) - KECUALIKAN YANG DIBATALKAN
        $itemData = TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('stores', 'transactions.store_id', '=', 'stores.id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.status', '!=', 'dibatalkan') // <--- Proteksi keuangan sepihak
            ->when($startDate, fn($q) => $q->whereDate('transactions.created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('transactions.created_at', '<=', $endDate))
            ->when($storeId, fn($q) => $q->where('transactions.store_id', $storeId))
            ->selectRaw('
                SUM(transaction_items.price * transaction_items.quantity) as total_omset,
                SUM((SELECT price FROM product_prices WHERE product_id = transaction_items.product_id AND store_id = transactions.store_id LIMIT 1) * transaction_items.quantity) as total_hpp,
                SUM((transaction_items.price * transaction_items.quantity) * (stores.admin_fee / 100)) as total_admin_fee
            ')->first();

        // 3. Data Transaksi (Biaya Proses) - KECUALIKAN YANG DIBATALKAN
        $transactionData = Transaction::query()
            ->join('stores', 'transactions.store_id', '=', 'stores.id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.status', '!=', 'dibatalkan')
            ->when($startDate, fn($q) => $q->whereDate('transactions.created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('transactions.created_at', '<=', $endDate))
            ->when($storeId, fn($q) => $q->where('transactions.store_id', $storeId))
            ->selectRaw('SUM(stores.processing_fee) as total_proc_fee, COUNT(transactions.id) as total_orders')
            ->first();

        $omset = (float) $itemData->total_omset;
        $hpp = (float) $itemData->total_hpp;
        $adminFee = (float) $itemData->total_admin_fee;
        $procFee = (float) $transactionData->total_proc_fee;
        $adsFee = (float) $totalAdsCost;

        $margin = $omset - $hpp - $adminFee - $procFee - $adsFee;
        $percentage = $omset > 0 ? ($margin / $omset) * 100 : 0;

        // 4. DATA CHART TREND (7 Hari Terakhir)
        $marginChartTrend = $period->map(function ($date) use ($userId, $storeId) {
            $dayItems = TransactionItem::query()
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->join('stores', 'transactions.store_id', '=', 'stores.id')
                ->where('transactions.user_id', $userId)
                ->where('transactions.status', '!=', 'dibatalkan')
                ->whereDate('transactions.created_at', $date)
                ->when($storeId, fn($q) => $q->where('transactions.store_id', $storeId))
                ->selectRaw('
                    SUM(transaction_items.price * transaction_items.quantity) as omset,
                    SUM((SELECT price FROM product_prices WHERE product_id = transaction_items.product_id AND store_id = transactions.store_id LIMIT 1) * transaction_items.quantity) as hpp,
                    SUM((transaction_items.price * transaction_items.quantity) * (stores.admin_fee / 100)) as admin
                ')->first();

            $dayProc = Transaction::query()
                ->join('stores', 'transactions.store_id', '=', 'stores.id')
                ->where('transactions.user_id', $userId)
                ->where('transactions.status', '!=', 'dibatalkan')
                ->whereDate('transactions.created_at', $date)
                ->when($storeId, fn($q) => $q->where('transactions.store_id', $storeId))
                ->sum('stores.processing_fee');

            $dayAds = AdsCost::where('user_id', $userId)
                ->whereDate('created_at', $date)
                ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->sum('amount');

            return ($dayItems->omset ?? 0) - ($dayItems->hpp ?? 0) - ($dayItems->admin ?? 0) - $dayProc - $dayAds;
        })->toArray();

        $omsetChartTrend = $period->map(function ($date) use ($userId, $storeId) {
            return Transaction::where('user_id', $userId)
                ->where('status', '!=', 'dibatalkan')
                ->whereDate('created_at', $date)
                ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                ->sum('total_price');
        })->toArray();

        return [
            Stat::make('Omset Bersih', 'Rp ' . number_format($omset, 0, ',', '.'))
                ->description($endDate ? 'Hingga ' . $chartEndDate->format('d M') : 'Trend 7 hari')
                ->chart($omsetChartTrend)
                ->color('info'),

            Stat::make('Margin Bersih (EBIT)', 'Rp ' . number_format($margin, 0, ',', '.'))
                ->description($adsFee > 0 ? 'Potong iklan Rp ' . number_format($adsFee, 0, ',', '.') : 'Profit bersih real')
                ->descriptionIcon($margin >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($marginChartTrend)
                ->color($margin >= 0 ? 'success' : 'danger'),

            Stat::make('Net Profit Margin', number_format($percentage, 1, ',', '.') . '%')
                ->description($percentage > 20 ? 'Performa sehat' : ($percentage > 10 ? 'Margin aman' : 'Margin kritis'))
                ->color($percentage > 20 ? 'success' : ($percentage > 10 ? 'warning' : 'danger')),
        ];
    }
}
