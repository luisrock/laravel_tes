<?php

use App\Enums\NewsletterEventAction;
use App\Enums\NewsletterEventSource;
use App\Models\NewsletterSubscriptionEvent;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('registered', 'web');

    config()->set('services.sendy.api_base_url', 'https://sendy.test');
    config()->set('services.sendy.api_token', 'test-token');
    config()->set('services.sendy.list_id', 'list-hash');
    config()->set('services.sendy.list_internal_id', 2);
});

it('registra usuário e exibe toast subscribed quando Sendy aceita inscrição', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/subscribe' => Http::response('true'),
    ]);

    $this->post('/register', [
        'name' => 'Usuário Newsletter',
        'email' => 'novo.newsletter@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])
        ->assertRedirect()
        ->assertSessionHas('newsletter.registration_toast', 'subscribed');

    expect(User::query()->where('email', 'novo.newsletter@gmail.com')->exists())->toBeTrue();

    Http::assertSent(fn ($request) => $request->url() === 'https://sendy.test/subscribe');

    expect(NewsletterSubscriptionEvent::query()
        ->where('email', 'novo.newsletter@gmail.com')
        ->where('action', NewsletterEventAction::Subscribed->value)
        ->where('source', NewsletterEventSource::Registration->value)
        ->exists())->toBeTrue();
});

it('registra usuário e exibe toast subscribed quando email já está na lista', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/subscribe' => Http::response('Already subscribed.'),
    ]);

    $this->post('/register', [
        'name' => 'Usuário Já Inscrito',
        'email' => 'ja.inscrito@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])
        ->assertRedirect()
        ->assertSessionHas('newsletter.registration_toast', 'subscribed');

    expect(User::query()->where('email', 'ja.inscrito@gmail.com')->exists())->toBeTrue();
});

it('registra usuário e exibe toast invite quando Sendy falha', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/subscribe' => Http::response('Some error', 500),
    ]);

    $this->post('/register', [
        'name' => 'Usuário Falha Sendy',
        'email' => 'falha.sendy@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])
        ->assertRedirect()
        ->assertSessionHas('newsletter.registration_toast', 'invite');

    expect(User::query()->where('email', 'falha.sendy@gmail.com')->exists())->toBeTrue();
});

it('registra usuário sem toast quando integração está desligada', function () {
    SiteSetting::set('newsletter_integration_enabled', '0');

    Http::fake();

    $this->post('/register', [
        'name' => 'Usuário Sem Newsletter',
        'email' => 'sem.newsletter@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])
        ->assertRedirect()
        ->assertSessionMissing('newsletter.registration_toast');

    expect(User::query()->where('email', 'sem.newsletter@gmail.com')->exists())->toBeTrue();

    Http::assertNothingSent();
});

it('exibe toast subscribed no layout do painel quando há flash na sessão', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)
        ->withSession(['newsletter.registration_toast' => 'subscribed'])
        ->get(route('user-panel.dashboard'))
        ->assertSuccessful()
        ->assertSee('Você foi inscrito na nossa newsletter semanal', false)
        ->assertSee('newsletter-registration-toast', false);
});

it('exibe toast na página de verificação de email após registro', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/subscribe' => Http::response('true'),
    ]);

    $this->post('/register', [
        'name' => 'Verificar Email Toast',
        'email' => 'verificar.toast@gmail.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])
        ->assertRedirect();

    $this->get(route('verification.notice'))
        ->assertSuccessful()
        ->assertSee('Você foi inscrito na nossa newsletter semanal', false);
});

it('exibe toast invite no layout do painel quando inscrição falhou', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)
        ->withSession(['newsletter.registration_toast' => 'invite'])
        ->get(route('user-panel.dashboard'))
        ->assertSuccessful()
        ->assertSee('Inscreva-se também para receber a newsletter de atualização semanal', false);
});
