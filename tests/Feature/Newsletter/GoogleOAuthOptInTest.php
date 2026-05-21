<?php

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('registered', 'web');

    config()->set('services.sendy.api_base_url', 'https://sendy.test');
    config()->set('services.sendy.api_token', 'test-token');
    config()->set('services.sendy.list_id', 'list-hash');
    config()->set('services.sendy.list_internal_id', 2);
});

it('novo usuário Google recebe toast subscribed quando Sendy aceita', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/subscribe' => Http::response('true'),
    ]);

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-newsletter-1',
        'name' => 'Google Newsletter',
        'email' => 'google.newsletter@gmail.com',
    ]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect(config('fortify.home'))
        ->assertSessionHas('newsletter.registration_toast', 'subscribed');

    Http::assertSent(fn ($request) => $request->url() === 'https://sendy.test/subscribe');
});

it('novo usuário Google recebe toast subscribed quando já está na lista', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/subscribe' => Http::response('Already subscribed.'),
    ]);

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-already-1',
        'name' => 'Google Já Inscrito',
        'email' => 'google.ja.inscrito@gmail.com',
    ]));

    $this->get(route('auth.google.callback'))
        ->assertSessionHas('newsletter.registration_toast', 'subscribed');
});

it('novo usuário Google recebe toast invite quando Sendy falha', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/subscribe' => Http::response('error', 500),
    ]);

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-fail-1',
        'name' => 'Google Falha',
        'email' => 'google.falha@gmail.com',
    ]));

    $this->get(route('auth.google.callback'))
        ->assertSessionHas('newsletter.registration_toast', 'invite');

    expect(User::query()->where('email', 'google.falha@gmail.com')->exists())->toBeTrue();
});

it('relogin de usuário Google existente não dispara newsletter nem toast', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake();

    $user = User::factory()->create([
        'email' => 'google.existente@gmail.com',
        'google_id' => 'google-existente-id',
        'email_verified_at' => now(),
    ]);

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-existente-id',
        'name' => 'Google Existente',
        'email' => 'google.existente@gmail.com',
    ]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect(config('fortify.home'))
        ->assertSessionMissing('newsletter.registration_toast');

    Http::assertNothingSent();
});

it('novo usuário Google sem integração ligada não dispara Sendy nem toast', function () {
    SiteSetting::set('newsletter_integration_enabled', '0');

    Http::fake();

    Socialite::fake('google', (new SocialiteUser)->map([
        'id' => 'google-off-1',
        'name' => 'Google Off',
        'email' => 'google.off@gmail.com',
    ]));

    $this->get(route('auth.google.callback'))
        ->assertSessionMissing('newsletter.registration_toast');

    Http::assertNothingSent();
});
