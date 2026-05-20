<?php

use App\Livewire\NewsletterToggle;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

beforeEach(function () {
    config()->set('services.sendy.api_base_url', 'https://sendy.test');
    config()->set('services.sendy.api_token', 'test-token');
    config()->set('services.sendy.list_id', 'list-hash');
    config()->set('services.sendy.list_internal_id', 2);
});

it('exibe toggle na página de perfil quando flag ligada', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('user-panel.profile'))
        ->assertSuccessful()
        ->assertSee('Email semanal de atualização', false)
        ->assertSeeLivewire(NewsletterToggle::class);
});

it('subscribe via Livewire atualiza estado e chama API', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/subscribe' => Http::response('true'),
        'https://sendy.test/api/subscribers/subscription-status.php' => Http::response('Unsubscribed'),
    ]);

    $user = User::factory()->create(['newsletter_subscribed_at' => null]);

    Livewire::actingAs($user)
        ->test(NewsletterToggle::class)
        ->call('subscribe')
        ->assertSet('subscribed', true)
        ->assertSet('messageType', 'success');

    Http::assertSent(fn ($request) => $request->url() === 'https://sendy.test/subscribe');
});

it('unsubscribe via Livewire atualiza estado e chama API', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/unsubscribe' => Http::response('true'),
        'https://sendy.test/api/subscribers/subscription-status.php' => Http::response('Subscribed'),
    ]);

    $user = User::factory()->create(['newsletter_subscribed_at' => now()]);

    Livewire::actingAs($user)
        ->test(NewsletterToggle::class)
        ->call('unsubscribe')
        ->assertSet('subscribed', false)
        ->assertSet('messageType', 'success');

    Http::assertSent(fn ($request) => $request->url() === 'https://sendy.test/unsubscribe');
});

it('sync no mount detecta inscrito via API Sendy', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/api/subscribers/subscription-status.php' => Http::response('Subscribed'),
    ]);

    $user = User::factory()->create(['newsletter_subscribed_at' => null]);

    Livewire::actingAs($user)
        ->test(NewsletterToggle::class)
        ->assertSet('subscribed', true);

    expect($user->fresh()->newsletter_subscribed_at)->not->toBeNull();
});

it('não chama subscribe quando flag desligada', function () {
    SiteSetting::set('newsletter_integration_enabled', '0');

    Http::fake();

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(NewsletterToggle::class)
        ->call('subscribe')
        ->assertSet('messageType', 'error');

    Http::assertNothingSent();
});
