<?php

namespace App\Filament\Widgets;

use App\Services\Newsletter\SiteMetrics;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class NewsletterPopupAbStats extends Widget
{
    use InteractsWithPageFilters;

    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected static ?int $sort = 10;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.newsletter-popup-ab-stats';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $period = (string) ($this->pageFilters['period'] ?? '30');

        return [
            'rows' => SiteMetrics::popupVariantStats($period),
            'periodLabel' => SiteMetrics::periodLabel($period),
        ];
    }
}
