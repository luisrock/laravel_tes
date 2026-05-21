<?php

namespace App\Filament\Pages;

/**
 * Redireciona URL antiga para a página de estatísticas gerais.
 */
class NewsletterStats extends SiteStats
{
    protected static ?string $slug = 'newsletter-stats';

    protected static bool $shouldRegisterNavigation = false;

    public function mount(): void
    {
        $this->redirect(SiteStats::getUrl(), navigate: true);
    }
}
