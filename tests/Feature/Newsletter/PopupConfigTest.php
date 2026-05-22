<?php

use App\Filament\Pages\NewsletterPopupSettings;
use App\Models\SiteSetting;
use App\Models\User;

beforeEach(function () {
    config()->set('services.sendy.list_internal_id', 2);
    SiteSetting::set('newsletter_integration_enabled', '0');
    SiteSetting::set('newsletter_popup_enabled', '0');
});

function popupHomeGet(): \Illuminate\Testing\TestResponse
{
    return test()->get(route('searchpage'));
}

it('não inclui popup quando integração está desligada', function () {
    SiteSetting::set('newsletter_integration_enabled', '0');
    SiteSetting::set('newsletter_popup_enabled', '1');

    popupHomeGet()
        ->assertSuccessful()
        ->assertDontSee('newsletterPopup(', false)
        ->assertDontSee('data-testid="newsletter-popup"', false);
});

it('não inclui popup quando popup está desligado', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');
    SiteSetting::set('newsletter_popup_enabled', '0');

    popupHomeGet()
        ->assertSuccessful()
        ->assertDontSee('newsletterPopup(', false)
        ->assertDontSee('data-testid="newsletter-popup"', false);
});

it('inclui popup para visitante quando integração e popup estão ligados', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');
    SiteSetting::set('newsletter_popup_enabled', '1');

    popupHomeGet()
        ->assertSuccessful()
        ->assertSee('newsletterPopup(', false)
        ->assertSee('data-testid="newsletter-popup"', false);
});

it('inclui epoch de reset na config do popup', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');
    SiteSetting::set('newsletter_popup_enabled', '1');
    SiteSetting::set('newsletter_popup_dismiss_reset_epoch', '999001');

    popupHomeGet()
        ->assertSuccessful()
        ->assertSee('dismissResetEpoch', false)
        ->assertSee('999001', false);
});

it('resetDismissWait atualiza newsletter_popup_dismiss_reset_epoch', function () {
    SiteSetting::set('newsletter_popup_dismiss_reset_epoch', '0');

    $page = app(NewsletterPopupSettings::class);
    $page->resetDismissWait();

    expect((int) SiteSetting::get('newsletter_popup_dismiss_reset_epoch'))->toBeGreaterThan(0);
});

it('resetPopupTestCookies atualiza epochs de dismiss e subscribed', function () {
    SiteSetting::set('newsletter_popup_dismiss_reset_epoch', '0');
    SiteSetting::set('newsletter_popup_subscribed_reset_epoch', '0');

    $page = app(NewsletterPopupSettings::class);
    $page->resetPopupTestCookies();

    expect((int) SiteSetting::get('newsletter_popup_dismiss_reset_epoch'))->toBeGreaterThan(0)
        ->and((int) SiteSetting::get('newsletter_popup_subscribed_reset_epoch'))->toBeGreaterThan(0);
});

it('inclui popup para utilizador autenticado não inscrito no Sendy', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');
    SiteSetting::set('newsletter_popup_enabled', '1');
    fakeSendyConnection();

    $user = User::factory()->create([
        'newsletter_subscribed_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('searchpage'))
        ->assertSuccessful()
        ->assertSee('newsletterPopup(', false)
        ->assertSee('data-testid="newsletter-popup"', false)
        ->assertSee($user->email, false)
        ->assertSee('emailReadonly', false);
});

it('não inclui popup para utilizador autenticado inscrito no Sendy', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');
    SiteSetting::set('newsletter_popup_enabled', '1');
    fakeSendyConnection();

    $user = User::factory()->create([
        'newsletter_subscribed_at' => null,
    ]);

    seedSendyActiveSubscriber($user->email);

    $this->actingAs($user)
        ->get(route('searchpage'))
        ->assertSuccessful()
        ->assertDontSee('newsletterPopup(', false)
        ->assertDontSee('data-testid="newsletter-popup"', false);
});

it('não inclui popup para logado com cache de inscrição quando Sendy falha', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');
    SiteSetting::set('newsletter_popup_enabled', '1');

    $user = User::factory()->create([
        'newsletter_subscribed_at' => now(),
    ]);

    bindNewsletterCheckerMock(function ($mock): void {
        $mock->shouldReceive('isEnabled')->andReturn(true);
        $mock->shouldReceive('isSubscribed')->once()->andThrow(new RuntimeException('Sendy indisponível'));
    });

    $this->actingAs($user)
        ->get(route('searchpage'))
        ->assertSuccessful()
        ->assertDontSee('newsletterPopup(', false)
        ->assertDontSee('data-testid="newsletter-popup"', false);
});

it('inclui popup para logado sem cache quando Sendy falha', function () {
    SiteSetting::set('newsletter_integration_enabled', '1');
    SiteSetting::set('newsletter_popup_enabled', '1');

    $user = User::factory()->create([
        'newsletter_subscribed_at' => null,
    ]);

    bindNewsletterCheckerMock(function ($mock): void {
        $mock->shouldReceive('isEnabled')->andReturn(true);
        $mock->shouldReceive('isSubscribed')->once()->andThrow(new RuntimeException('Sendy indisponível'));
    });

    $this->actingAs($user)
        ->get(route('searchpage'))
        ->assertSuccessful()
        ->assertSee('newsletterPopup(', false)
        ->assertSee('data-testid="newsletter-popup"', false);
});

describe('Acesso à página NewsletterPopupSettings', function () {

    it('redireciona visitante não autenticado', function () {
        $this->get('/admin/painel/newsletter-popup-settings')
            ->assertRedirect();
    });

    it('retorna 403 para usuário comum', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/painel/newsletter-popup-settings')
            ->assertForbidden();
    });

    it('admin consegue acessar a página', function () {
        $admin = createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/painel/newsletter-popup-settings');

        expect($response->getStatusCode())->toBeIn([200, 302]);
    });

});
