<?php

namespace App\Filament\Widgets;

use App\Models\RefundRequest;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class SubscriptionStats extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        $activeCount = DB::table('subscriptions')
            ->where('stripe_status', 'active')
            ->whereNull('ends_at')
            ->count();

        $graceCount = DB::table('subscriptions')
            ->whereNotNull('ends_at')
            ->where('ends_at', '>', now())
            ->count();

        $pendingRefunds = RefundRequest::query()
            ->where('status', RefundRequest::STATUS_PENDING)
            ->count();

        return [
            Card::make('Assinantes ativos', $activeCount),
            Card::make('Grace period', $graceCount),
            Card::make('Estornos pendentes', $pendingRefunds),
            Card::make('Stripe Dashboard', 'Abrir')
                ->url($this->getStripeDashboardBaseUrl(), true),
        ];
    }

    protected function getStripeDashboardBaseUrl(): string
    {
        $secret = (string) config('cashier.secret');
        $isTestMode = str_starts_with($secret, 'sk_test');

        return $isTestMode ? 'https://dashboard.stripe.com/test' : 'https://dashboard.stripe.com';
    }
}
