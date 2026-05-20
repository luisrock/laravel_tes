<?php

use App\Enums\NewsletterEventSource;
use App\Jobs\Newsletter\SubscribeToSendyJob;
use App\Jobs\Newsletter\SyncNewsletterStatusJob;
use App\Jobs\Newsletter\UnsubscribeFromSendyJob;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\Sendy\NewsletterSubscriptionContext;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config()->set('services.sendy.api_base_url', 'https://sendy.test');
    config()->set('services.sendy.api_token', 'test-token');
    config()->set('services.sendy.list_id', 'list-hash');
    config()->set('services.sendy.list_internal_id', 2);
});

it('SubscribeToSendyJob chama API subscribe quando integração ligada', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/subscribe' => Http::response('true'),
    ]);

    $ctx = new NewsletterSubscriptionContext(
        source: NewsletterEventSource::Registration,
        userId: null,
    );

    SubscribeToSendyJob::dispatchSync('user@example.com', 'User Name', $ctx);

    Http::assertSent(fn ($request) => $request->url() === 'https://sendy.test/subscribe'
        && $request['email'] === 'user@example.com');
});

it('UnsubscribeFromSendyJob chama API unsubscribe quando integração ligada', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/unsubscribe' => Http::response('true'),
    ]);

    $ctx = new NewsletterSubscriptionContext(
        source: NewsletterEventSource::PanelToggle,
        userId: 1,
    );

    UnsubscribeFromSendyJob::dispatchSync('user@example.com', $ctx);

    Http::assertSent(fn ($request) => $request->url() === 'https://sendy.test/unsubscribe'
        && $request['email'] === 'user@example.com');
});

it('SubscribeToSendyJob pode ser enfileirado', function () {
    Queue::fake();

    $ctx = new NewsletterSubscriptionContext(source: NewsletterEventSource::Registration);

    SubscribeToSendyJob::dispatch('queued@example.com', 'Queued', $ctx);

    Queue::assertPushed(SubscribeToSendyJob::class);
});

it('SyncNewsletterStatusJob não falha sem colunas de newsletter no users', function () {
    $user = User::factory()->create();

    SyncNewsletterStatusJob::dispatchSync($user->id);

    expect($user->fresh())->not->toBeNull();
});
