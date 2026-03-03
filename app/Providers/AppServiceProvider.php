<?php

namespace App\Providers;

 use App\Models\User;
 use App\Models\OrderSale;
 use App\Observers\UserObserver;
 use App\Observers\OrderSaleObserver;
 use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        // Register Observers
        User::observe(UserObserver::class);
        OrderSale::observe(OrderSaleObserver::class);
     }
}
