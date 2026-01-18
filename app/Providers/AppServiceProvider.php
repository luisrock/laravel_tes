<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() == 'local') {
            $this->app->register(\Reliese\Coders\CodersServiceProvider::class);
        }

        // Registrar services como singletons
        $this->app->singleton(\App\Services\StripeService::class);
        $this->app->singleton(\App\Services\SubscriptionService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Pagination\Paginator::useBootstrap();

        // Validar configuração crítica de assinaturas em produção
        if ($this->app->environment('production')) {
            $tierProductIds = config('subscription.tier_product_ids', []);
            
            if (empty($tierProductIds)) {
                Log::critical('STRIPE_PRODUCT_PRO e STRIPE_PRODUCT_PREMIUM não configurados!');
            }
        }
    }
}

