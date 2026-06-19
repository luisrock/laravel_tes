<?php

namespace App\Filament\Widgets;

use App\Models\ExtensionUsageDaily;
use App\Services\Newsletter\SiteMetrics;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ExtensionUsageStats extends StatsOverviewWidget
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
        $topVersion = ExtensionUsageDaily::topVersion($period);

        return [
            Stat::make('Buscas da extensão', number_format(ExtensionUsageDaily::totalHits($period), 0, ',', '.'))
                ->description($periodLabel)
                ->descriptionIcon('heroicon-m-puzzle-piece'),
            Stat::make('Média diária', number_format(ExtensionUsageDaily::dailyAverage($period), 1, ',', '.'))
                ->description($periodLabel.' · buscas/dia')
                ->descriptionIcon('heroicon-m-puzzle-piece'),
            Stat::make('Versão mais usada', $topVersion ?? '—')
                ->description('Por nº de buscas no período')
                ->descriptionIcon('heroicon-m-puzzle-piece'),
        ];
    }
}
