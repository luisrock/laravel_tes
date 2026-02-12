<?php

namespace App\Providers;

use Reliese\Coders\CodersServiceProvider;
use App\Services\StripeService;
use App\Services\SubscriptionService;
use Illuminate\Pagination\Paginator;
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
            $this->app->register(CodersServiceProvider::class);
        }

        // Registrar services como singletons
        $this->app->singleton(StripeService::class);
        $this->app->singleton(SubscriptionService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        // Validar configuração crítica de assinaturas em produção
        if ($this->app->environment('production')) {
            $tierProductIds = config('subscription.tier_product_ids', []);
            
            if (empty($tierProductIds)) {
                Log::critical('STRIPE_PRODUCT_PRO e STRIPE_PRODUCT_PREMIUM não configurados!');
            }
        }
    }
}

