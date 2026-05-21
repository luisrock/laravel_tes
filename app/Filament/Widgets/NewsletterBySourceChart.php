<?php

namespace App\Filament\Widgets;

use App\Services\Newsletter\SiteMetrics;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class NewsletterBySourceChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 1;

    protected ?string $heading = 'De onde vieram as inscrições';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $period = (string) ($this->pageFilters['period'] ?? '30');
        $series = SiteMetrics::subscriptionsBySource($period);

        return [
            'datasets' => [
                [
                    'label' => 'Inscrições',
                    'data' => $series['data'],
                    'backgroundColor' => [
                        '#912F56',
                        '#b84d75',
                        '#d97a9a',
                        '#64748b',
                        '#475569',
                        '#334155',
                    ],
                ],
            ],
            'labels' => $series['labels'],
        ];
    }
}
