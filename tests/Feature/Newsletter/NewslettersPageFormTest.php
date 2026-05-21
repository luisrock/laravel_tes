<?php

use App\Enums\NewsletterEventAction;
use App\Enums\NewsletterEventSource;
use App\Models\Newsletter;
use App\Models\NewsletterSubscriptionEvent;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Config::set('honeypot.enabled', false);
    config()->set('services.sendy.api_base_url', 'https://sendy.test');
    config()->set('services.sendy.api_token', 'test-token');
    config()->set('services.sendy.list_id', 'list-hash');
    config()->set('services.sendy.list_internal_id', 2);

    Newsletter::create([
        'subject' => 'Newsletter Teste',
        'slug' => 'newsletter-teste-'.uniqid(),
        'plain_text' => 'Conteúdo de teste',
        'sent_at' => now(),
    ]);
});

function newslettersPageGet(): \Illuminate\Testing\TestResponse
{
    return test()->get(route('newsletterspage'));
}

it('não exibe inscrição quando flag está desligada', function () {
    SiteSetting::set('newsletter_integration_enabled', '0');

    newslettersPageGet()
        ->assertSuccessful()
        ->assertDontSee('Receba atualização semanal', false)
        ->assertDontSee('placeholder="Nome"', false)
        ->assertDontSee('newsletter.maurolopes.com.br/subscription', false);
});

it('exibe form compacto para visitante quando flag está ligada', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    newslettersPageGet()
        ->assertSuccessful()
        ->assertSee('placeholder="Nome"', false)
        ->assertSee('placeholder="E-mail"', false)
        ->assertSee('newsletterForm()', false);
});

it('exibe Você está inscrito para user inscrito no Sendy', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/api/subscribers/subscription-status.php' => Http::response('Subscribed'),
    ]);

    $user = User::factory()->create([
        'newsletter_subscribed_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('newsletterspage'))
        ->assertSuccessful()
        ->assertSee('Você está inscrito!', false)
        ->assertDontSee('Receba atualização semanal', false);
});

it('logado não inscrito vê link Receba atualização semanal', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/api/subscribers/subscription-status.php' => Http::response('Unsubscribed'),
    ]);

    $user = User::factory()->create(['newsletter_subscribed_at' => null]);

    $this->actingAs($user)
        ->get(route('newsletterspage'))
        ->assertSuccessful()
        ->assertSee('Receba atualização semanal', false)
        ->assertSee('newsletterQuickSubscribe()', false)
        ->assertDontSee('placeholder="Nome"', false);
});

it('POST subscribe válido retorna JSON success e grava evento', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/subscribe' => Http::response('true'),
    ]);

    $this->postJson(route('newsletter.subscribe'), [
        'name' => 'Visitante',
        'email' => 'visitante@gmail.com',
    ])
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'already_subscribed' => false,
        ]);

    expect(NewsletterSubscriptionEvent::query()
        ->where('email', 'visitante@gmail.com')
        ->where('action', NewsletterEventAction::Subscribed->value)
        ->where('source', NewsletterEventSource::NewslettersForm->value)
        ->exists())->toBeTrue();
});

it('POST subscribe retorna 503 quando flag desligada', function () {
    SiteSetting::set('newsletter_integration_enabled', '0');

    $this->postJson(route('newsletter.subscribe'), [
        'name' => 'Visitante',
        'email' => 'visitante@gmail.com',
    ])
        ->assertStatus(503)
        ->assertJson(['success' => false]);
});

it('POST subscribe retorna 403 quando user logado envia email diferente', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    $user = User::factory()->create(['email' => 'real@example.com']);

    $this->actingAs($user)
        ->postJson(route('newsletter.subscribe'), [
            'name' => 'Atacante',
            'email' => 'outro@example.com',
        ])
        ->assertForbidden();
});

it('aplica rate limit de 5 requisições por minuto', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/subscribe' => Http::response('true'),
    ]);

    for ($i = 0; $i < 5; $i++) {
        $this->postJson(route('newsletter.subscribe'), [
            'name' => 'Rate',
            'email' => "rate.test.{$i}@gmail.com",
        ])->assertSuccessful();
    }

    $this->postJson(route('newsletter.subscribe'), [
        'name' => 'Rate',
        'email' => 'rate.test.6@gmail.com',
    ])->assertStatus(429);
});

it('rejeita email inválido no subscribe', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake();

    $this->postJson(route('newsletter.subscribe'), [
        'name' => 'Visitante',
        'email' => 'nao-e-um-email',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);

    Http::assertNothingSent();
});

it('rejeita honeypot quando habilitado', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');
    Config::set('honeypot.enabled', true);
    Config::set('honeypot.randomize_name_field_name', false);
    Config::set('honeypot.valid_from_timestamp', false);

    Http::fake();

    $this->postJson(route('newsletter.subscribe'), [
        'name' => 'Bot',
        'email' => 'bot.spam@gmail.com',
        config('honeypot.name_field_name') => 'spam-value',
    ])->assertOk();

    Http::assertNothingSent();

    expect(NewsletterSubscriptionEvent::query()->where('email', 'bot.spam@gmail.com')->exists())->toBeFalse();
});
