<?php

namespace App\Providers;

use App\Support\CustomLoadingIndicator;
use Filament\Support\Contracts\LoadingIndicator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 3. Bind contract ke class kustom sesuai dokumentasi v5
        $this->app->bind(LoadingIndicator::class, CustomLoadingIndicator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
