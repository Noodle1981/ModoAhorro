<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\EquipmentUsage;
use App\Observers\EquipmentUsageObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        EquipmentUsage::observe(EquipmentUsageObserver::class);
    }
}
