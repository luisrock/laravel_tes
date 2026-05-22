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
});

function newsletterShowGet(Newsletter $campaign): \Illuminate\Testing\TestResponse
{
    return test()->get(route('newsletter.show', $campaign->slug));
}

it('não exibe card de inscrição quando flag está desligada', function () {
    SiteSetting::set('newsletter_integration_enabled', '0');

    $campaign = Newsletter::create([
        'subject' => 'Edição Teste',
        'slug' => 'edicao-teste-'.uniqid(),
        'html_content' => '<p>Conteúdo</p>',
        'plain_text' => 'Conteúdo',
        'sent_at' => now(),
    ]);

    newsletterShowGet($campaign)
        ->assertSuccessful()
        ->assertDontSee('Gostou?', false)
        ->assertDontSee('newsletter.maurolopes.com.br/subscription', false);
});

it('exibe form no card Gostou quando flag está ligada', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    $campaign = Newsletter::create([
        'subject' => 'Edição Teste',
        'slug' => 'edicao-form-'.uniqid(),
        'html_content' => '<p>Conteúdo</p>',
        'plain_text' => 'Conteúdo',
        'sent_at' => now(),
    ]);

    newsletterShowGet($campaign)
        ->assertSuccessful()
        ->assertSee('Gostou?', false)
        ->assertSee('newsletter-sidebar-name', false)
        ->assertSee('newsletter-sidebar-email', false)
        ->assertSee('Inscrever-se Agora', false)
        ->assertSee('newsletterForm()', false)
        ->assertDontSee('newsletter.maurolopes.com.br/subscription', false);
});

it('exibe Você está inscrito para user inscrito no Sendy', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/api/subscribers/subscription-status.php' => Http::response('Subscribed'),
    ]);

    $campaign = Newsletter::create([
        'subject' => 'Edição Inscrito',
        'slug' => 'edicao-inscrito-'.uniqid(),
        'html_content' => '<p>Conteúdo</p>',
        'plain_text' => 'Conteúdo',
        'sent_at' => now(),
    ]);

    $user = User::factory()->create(['newsletter_subscribed_at' => null]);

    test()->actingAs($user)
        ->get(route('newsletter.show', $campaign->slug))
        ->assertSuccessful()
        ->assertSee('Você está inscrito!', false)
        ->assertDontSee('newsletter-sidebar-email', false);
});

it('POST subscribe da página de edição grava evento newsletters_form', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');

    Http::fake([
        'https://sendy.test/subscribe' => Http::response('true'),
    ]);

    test()->postJson(route('newsletter.subscribe'), [
        'name' => 'Leitor',
        'email' => 'leitor.edicao@gmail.com',
    ])
        ->assertSuccessful()
        ->assertJson([
            'success' => true,
            'already_subscribed' => false,
        ]);

    expect(NewsletterSubscriptionEvent::query()
        ->where('email', 'leitor.edicao@gmail.com')
        ->where('action', NewsletterEventAction::Subscribed->value)
        ->where('source', NewsletterEventSource::NewslettersForm->value)
        ->exists())->toBeTrue();
});
