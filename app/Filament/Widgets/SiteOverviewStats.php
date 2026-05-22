<?php

namespace App\Filament\Widgets;

use App\Services\Newsletter\SiteMetrics;
use App\Services\Sendy\SendyService;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SiteOverviewStats extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected function getPeriod(): string
    {
        return (string) ($this->pageFilters['period'] ?? '30');
    }

    protected function getStats(): array
    {
        $period = $this->getPeriod();
        $periodLabel = SiteMetrics::periodLabel($period);
        $sendyCount = app(SendyService::class)->activeSubscriberCount();
        $popupRate = SiteMetrics::popupConversionRate($period);
        $newsletterPagesCount = SiteMetrics::newsletterPagesSubscriptions($period);

        return [
            Stat::make('Novos registos', (string) SiteMetrics::newUserRegistrations($period))
                ->description($periodLabel)
                ->descriptionIcon('heroicon-m-user-plus'),
            Stat::make('Novas inscrições na newsletter', (string) SiteMetrics::newSubscriptions($period))
                ->description($periodLabel)
                ->descriptionIcon('heroicon-m-envelope'),
            Stat::make('Inscrições (páginas newsletters)', (string) $newsletterPagesCount)
                ->description($periodLabel.' · /newsletters e edições')
                ->descriptionIcon('heroicon-m-newspaper'),
            Stat::make('Total na lista de email', $sendyCount !== null ? number_format($sendyCount, 0, ',', '.') : '—')
                ->description('Contactos ativos no Sendy (agora)')
                ->descriptionIcon('heroicon-m-users'),
            Stat::make('Contas inscritas no site', (string) SiteMetrics::cachedSubscribedUserCount())
                ->description('Utilizadores com conta marcados como inscritos')
                ->descriptionIcon('heroicon-m-check-badge'),
            Stat::make('Conversão do popup', $popupRate !== null ? "{$popupRate}%" : '—')
                ->description($periodLabel)
                ->descriptionIcon('heroicon-m-window'),
        ];
    }
}
