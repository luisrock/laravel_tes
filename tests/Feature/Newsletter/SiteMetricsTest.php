<?php

use App\Enums\NewsletterEventAction;
use App\Enums\NewsletterEventSource;
use App\Models\NewsletterSubscriptionEvent;
use App\Models\User;
use App\Services\Newsletter\SiteMetrics;

it('conta registos apenas dentro do período selecionado', function () {
    User::factory()->create(['created_at' => now()->subHours(30)]);
    User::factory()->create(['created_at' => now()->subHours(2)]);

    expect(SiteMetrics::newUserRegistrations('1'))->toBe(1)
        ->and(SiteMetrics::newUserRegistrations('3'))->toBe(2);
});

it('filtra popup A/B pelo período', function () {
    NewsletterSubscriptionEvent::factory()->create([
        'source' => 'popup',
        'popup_variant' => 'A',
        'action' => NewsletterEventAction::Impression->value,
        'created_at' => now()->subDays(40),
    ]);
    NewsletterSubscriptionEvent::factory()->create([
        'source' => 'popup',
        'popup_variant' => 'A',
        'action' => NewsletterEventAction::Impression->value,
        'created_at' => now()->subDay(),
    ]);

    $stats30 = SiteMetrics::popupVariantStats('30');
    $stats60 = SiteMetrics::popupVariantStats('60');

    expect($stats30[0]['impressions'])->toBe(1)
        ->and($stats60[0]['impressions'])->toBe(2);
});

it('conta inscrições das páginas de newsletters separadas do popup', function () {
    NewsletterSubscriptionEvent::factory()->create([
        'action' => NewsletterEventAction::Subscribed->value,
        'source' => NewsletterEventSource::NewslettersForm->value,
        'created_at' => now()->subDay(),
    ]);
    NewsletterSubscriptionEvent::factory()->create([
        'action' => NewsletterEventAction::Subscribed->value,
        'source' => NewsletterEventSource::Popup->value,
        'created_at' => now()->subDay(),
    ]);
    NewsletterSubscriptionEvent::factory()->create([
        'action' => NewsletterEventAction::Subscribed->value,
        'source' => NewsletterEventSource::NewslettersForm->value,
        'created_at' => now()->subDays(40),
    ]);

    expect(SiteMetrics::newsletterPagesSubscriptions('30'))->toBe(1)
        ->and(SiteMetrics::newSubscriptions('30'))->toBe(2);
});
