<?php

namespace App\Providers;

use App\Services\StripeService;
use App\Services\SubscriptionService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Reliese\Coders\CodersServiceProvider;

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
        Paginator::defaultView('pagination.custom');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');

        // Validar configuração crítica de assinaturas em produção
        if ($this->app->environment('production')) {
            $tierProductIds = config('subscription.tier_product_ids', []);

            if (empty($tierProductIds)) {
                Log::critical('STRIPE_PRODUCT_PRO e STRIPE_PRODUCT_PREMIUM não configurados!');
            }
        }
    }
}
