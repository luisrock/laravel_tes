<?php

use App\Enums\NewsletterEventAction;
use App\Enums\NewsletterEventSource;
use App\Models\NewsletterSubscriptionEvent;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Sendy\NewsletterSubscriptionContext;
use App\Services\Sendy\SendyService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.sendy.api_base_url', 'https://sendy.test');
    config()->set('services.sendy.api_token', 'test-token');
    config()->set('services.sendy.list_id', 'list-hash');
    config()->set('services.sendy.list_internal_id', 2);
});

it('factory cria evento de newsletter', function () {
    $event = NewsletterSubscriptionEvent::factory()->create([
        'email' => 'factory@example.com',
        'action' => NewsletterEventAction::Subscribed->value,
        'source' => NewsletterEventSource::Registration->value,
    ]);

    expect($event->email)->toBe('factory@example.com')
        ->and($event->action)->toBe(NewsletterEventAction::Subscribed->value);
});

describe('NewsletterSubscriptionEvent scopes', function () {
    it('scopeByAction filtra por ação', function () {
        NewsletterSubscriptionEvent::factory()->create(['action' => NewsletterEventAction::Subscribed->value]);
        NewsletterSubscriptionEvent::factory()->create(['action' => NewsletterEventAction::Failed->value]);

        expect(NewsletterSubscriptionEvent::query()->byAction(NewsletterEventAction::Subscribed)->count())->toBe(1);
    });

    it('scopeBySource filtra por origem', function () {
        NewsletterSubscriptionEvent::factory()->create(['source' => NewsletterEventSource::Popup->value]);
        NewsletterSubscriptionEvent::factory()->create(['source' => NewsletterEventSource::Registration->value]);

        expect(NewsletterSubscriptionEvent::query()->bySource(NewsletterEventSource::Popup)->count())->toBe(1);
    });

    it('scopeSubscriptions inclui subscribed e already_subscribed', function () {
        NewsletterSubscriptionEvent::factory()->create(['action' => NewsletterEventAction::Subscribed->value]);
        NewsletterSubscriptionEvent::factory()->create(['action' => NewsletterEventAction::AlreadySubscribed->value]);
        NewsletterSubscriptionEvent::factory()->create(['action' => NewsletterEventAction::Unsubscribed->value]);

        expect(NewsletterSubscriptionEvent::query()->subscriptions()->count())->toBe(2);
    });

    it('scopeInPeriod filtra por intervalo de datas', function () {
        NewsletterSubscriptionEvent::factory()->create(['created_at' => now()->subDays(10)]);
        NewsletterSubscriptionEvent::factory()->create(['created_at' => now()->subDay()]);

        $count = NewsletterSubscriptionEvent::query()
            ->inPeriod(now()->subDays(3), now())
            ->count();

        expect($count)->toBe(1);
    });
});

describe('User::wantsNewsletter', function () {
    it('retorna true quando newsletter_subscribed_at está preenchido', function () {
        $user = User::factory()->create([
            'newsletter_subscribed_at' => now(),
        ]);

        expect($user->wantsNewsletter())->toBeTrue();
    });

    it('retorna false quando newsletter_subscribed_at é null', function () {
        $user = User::factory()->create([
            'newsletter_subscribed_at' => null,
        ]);

        expect($user->wantsNewsletter())->toBeFalse();
    });
});

describe('SendyService persiste eventos e cache', function () {
    it('grava evento subscribed e atualiza user após subscribe success', function () {
        SiteSetting::set('newsletter_integration_enabled', '1');

        Http::fake([
            'https://sendy.test/subscribe' => Http::response('true'),
        ]);

        $user = User::factory()->create();
        $ctx = new NewsletterSubscriptionContext(
            source: NewsletterEventSource::Registration,
            userId: $user->id,
        );

        $result = app(SendyService::class)->subscribe($user->email, $user->name, $ctx);

        expect($result->success)->toBeTrue();

        $user->refresh();

        expect($user->wantsNewsletter())->toBeTrue()
            ->and($user->newsletter_synced_at)->not->toBeNull();

        $event = NewsletterSubscriptionEvent::query()
            ->where('email', $user->email)
            ->where('action', NewsletterEventAction::Subscribed->value)
            ->first();

        expect($event)->not->toBeNull()
            ->and($event->user_id)->toBe($user->id)
            ->and($event->source)->toBe(NewsletterEventSource::Registration->value);
    });

    it('grava evento failed quando API retorna erro', function () {
        SiteSetting::set('newsletter_integration_enabled', '1');

        Http::fake([
            'https://sendy.test/subscribe' => Http::response('Invalid email address'),
        ]);

        $user = User::factory()->create();
        $ctx = new NewsletterSubscriptionContext(
            source: NewsletterEventSource::NewslettersForm,
            userId: $user->id,
        );

        $result = app(SendyService::class)->subscribe($user->email, $user->name, $ctx);

        expect($result->success)->toBeFalse();

        $user->refresh();

        expect($user->wantsNewsletter())->toBeFalse();

        $event = NewsletterSubscriptionEvent::query()
            ->where('email', $user->email)
            ->where('action', NewsletterEventAction::Failed->value)
            ->first();

        expect($event)->not->toBeNull()
            ->and($event->meta)->toHaveKey('error');
    });

    it('grava evento already_subscribed quando API responde Already subscribed', function () {
        SiteSetting::set('newsletter_integration_enabled', '1');

        Http::fake([
            'https://sendy.test/subscribe' => Http::response('Already subscribed.'),
        ]);

        $user = User::factory()->create();
        $ctx = new NewsletterSubscriptionContext(
            source: NewsletterEventSource::PanelToggle,
            userId: $user->id,
        );

        $result = app(SendyService::class)->subscribe($user->email, $user->name, $ctx);

        expect($result->alreadySubscribed)->toBeTrue();

        $user->refresh();

        expect($user->wantsNewsletter())->toBeTrue();

        expect(NewsletterSubscriptionEvent::query()
            ->where('email', $user->email)
            ->where('action', NewsletterEventAction::AlreadySubscribed->value)
            ->exists())->toBeTrue();
    });
});
