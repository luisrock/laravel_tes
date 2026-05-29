<?php

use App\Ai\Tools\QuerySiteMetrics;
use App\Enums\NewsletterEventSource;
use App\Models\NewsletterSubscriptionEvent;
use App\Models\User;
use Laravel\Ai\Tools\Request;

function runMetricsTool(int $period): array
{
    $result = (new QuerySiteMetrics)->handle(new Request(['period' => $period]));

    return json_decode((string) $result, true);
}

it('returns the metrics snapshot as JSON for the given period', function () {
    User::factory()->count(3)->create();
    NewsletterSubscriptionEvent::factory()->count(5)->create(); // NewslettersForm + subscribed

    $metrics = runMetricsTool(30);

    expect($metrics['periodo_dias'])->toBe(30)
        ->and($metrics['periodo_label'])->toBe('Últimos 30 dias')
        ->and($metrics['novos_registos'])->toBe(3)
        ->and($metrics['novas_inscricoes_newsletter'])->toBe(5)
        ->and($metrics['inscricoes_via_paginas_newsletters'])->toBe(5)
        ->and($metrics)->toHaveKey('total_na_lista_de_email')
        ->and($metrics)->toHaveKey('conversao_popup_percent')
        ->and($metrics)->toHaveKey('inscricoes_por_fonte');
});

it('separates newsletter-page subscriptions from other sources', function () {
    NewsletterSubscriptionEvent::factory()->count(2)->create([
        'source' => NewsletterEventSource::Registration->value,
    ]);
    NewsletterSubscriptionEvent::factory()->count(3)->create([
        'source' => NewsletterEventSource::NewslettersForm->value,
    ]);

    $metrics = runMetricsTool(30);

    expect($metrics['novas_inscricoes_newsletter'])->toBe(5)
        ->and($metrics['inscricoes_via_paginas_newsletters'])->toBe(3);
});

it('falls back to 30 days for an unsupported period', function () {
    expect(runMetricsTool(999)['periodo_dias'])->toBe(30);
});

it('respects the period window', function () {
    NewsletterSubscriptionEvent::factory()->create(['created_at' => now()->subDays(45)]);
    NewsletterSubscriptionEvent::factory()->create(['created_at' => now()->subDay()]);

    expect(runMetricsTool(7)['novas_inscricoes_newsletter'])->toBe(1)
        ->and(runMetricsTool(60)['novas_inscricoes_newsletter'])->toBe(2);
});
