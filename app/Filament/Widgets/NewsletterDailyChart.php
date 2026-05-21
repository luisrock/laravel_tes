<?php

namespace App\Filament\Widgets;

use App\Services\Newsletter\SiteMetrics;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class NewsletterDailyChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        $period = (string) ($this->pageFilters['period'] ?? '30');

        return $period === '1'
            ? 'Inscrições na newsletter por hora (24 h)'
            : 'Inscrições na newsletter por dia';
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $period = (string) ($this->pageFilters['period'] ?? '30');
        $series = SiteMetrics::subscriptionTimeline($period);

        return [
            'datasets' => [
                [
                    'label' => 'Inscrições',
                    'data' => $series['data'],
                    'borderColor' => '#912F56',
                    'backgroundColor' => 'rgba(145, 47, 86, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $series['labels'],
        ];
    }
}
