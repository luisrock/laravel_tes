<?php

use App\Enums\NewsletterEventAction;
use App\Enums\NewsletterEventSource;
use App\Models\NewsletterSubscriptionEvent;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('honeypot.enabled', false);
    SiteSetting::set('newsletter_integration_enabled', '1');
});

it('grava evento impression via POST newsletter event', function () {
    $this->postJson(route('newsletter.event'), [
        'action' => NewsletterEventAction::Impression->value,
        'variant' => 'A',
        'trigger' => 'timer',
    ])
        ->assertSuccessful()
        ->assertJson(['ok' => true]);

    $event = NewsletterSubscriptionEvent::query()->latest('id')->first();

    expect($event)->not->toBeNull()
        ->and($event->email)->toBe('')
        ->and($event->action)->toBe(NewsletterEventAction::Impression->value)
        ->and($event->source)->toBe(NewsletterEventSource::Popup->value)
        ->and($event->popup_variant)->toBe('A')
        ->and($event->popup_trigger)->toBe('timer');
});

it('grava evento dismissed', function () {
    $this->postJson(route('newsletter.event'), [
        'action' => NewsletterEventAction::Dismissed->value,
        'variant' => 'B',
        'trigger' => 'scroll',
    ])->assertSuccessful();

    expect(NewsletterSubscriptionEvent::query()
        ->where('action', NewsletterEventAction::Dismissed->value)
        ->where('popup_variant', 'B')
        ->exists())->toBeTrue();
});

it('rejeita variant inválida com 422', function () {
    $this->postJson(route('newsletter.event'), [
        'action' => NewsletterEventAction::Impression->value,
        'variant' => 'C',
    ])
        ->assertUnprocessable();
});

it('rejeita action inválida com 422', function () {
    $this->postJson(route('newsletter.event'), [
        'action' => 'subscribed',
    ])
        ->assertUnprocessable();
});

it('retorna 503 quando integração está desligada', function () {
    SiteSetting::set('newsletter_integration_enabled', '0');

    $this->postJson(route('newsletter.event'), [
        'action' => NewsletterEventAction::Impression->value,
    ])
        ->assertStatus(503)
        ->assertJson(['ok' => false]);
});

it('aplica rate limit de 30 requisições por minuto', function () {
    for ($i = 0; $i < 30; $i++) {
        $this->postJson(route('newsletter.event'), [
            'action' => NewsletterEventAction::Impression->value,
        ])->assertSuccessful();
    }

    $this->postJson(route('newsletter.event'), [
        'action' => NewsletterEventAction::Impression->value,
    ])->assertStatus(429);
});

it('ignora segundo POST subscribe duplicado em menos de 60 segundos', function () {
    config()->set('services.sendy.api_base_url', 'https://sendy.test');
    config()->set('services.sendy.api_token', 'test-token');
    config()->set('services.sendy.list_id', 'list-hash');
    config()->set('services.sendy.list_internal_id', 2);

    \Illuminate\Support\Facades\Http::fake([
        'https://sendy.test/subscribe' => \Illuminate\Support\Facades\Http::response('true'),
    ]);

    $payload = [
        'name' => 'Ivan',
        'email' => 'dedup-test@gmail.com',
        'from_popup' => '1',
    ];

    $this->postJson(route('newsletter.subscribe'), $payload)->assertSuccessful();
    $this->postJson(route('newsletter.subscribe'), $payload)->assertSuccessful();

    \Illuminate\Support\Facades\Http::assertSentCount(1);
});

it('inscrição via popup grava source popup no evento', function () {
    config()->set('services.sendy.api_base_url', 'https://sendy.test');
    config()->set('services.sendy.api_token', 'test-token');
    config()->set('services.sendy.list_id', 'list-hash');
    config()->set('services.sendy.list_internal_id', 2);

    \Illuminate\Support\Facades\Http::fake([
        'https://sendy.test/subscribe' => \Illuminate\Support\Facades\Http::response('true'),
    ]);

    $this->postJson(route('newsletter.subscribe'), [
        'name' => 'Visitante Popup',
        'email' => 'popupvisitor@gmail.com',
        'from_popup' => '1',
        'popup_variant' => 'A',
        'popup_trigger' => 'timer',
    ])
        ->assertSuccessful()
        ->assertJson(['success' => true]);

    expect(NewsletterSubscriptionEvent::query()
        ->where('email', 'popupvisitor@gmail.com')
        ->where('source', NewsletterEventSource::Popup->value)
        ->where('popup_variant', 'A')
        ->where('popup_trigger', 'timer')
        ->exists())->toBeTrue();
});
