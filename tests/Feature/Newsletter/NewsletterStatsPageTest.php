<?php

use App\Enums\NewsletterEventAction;
use App\Enums\NewsletterEventSource;
use App\Models\NewsletterSubscriptionEvent;
use App\Models\User;
use App\Services\Newsletter\SiteMetrics;

describe('Acesso à página SiteStats', function () {

    it('redireciona visitante não autenticado', function () {
        $this->get('/admin/painel/estatisticas')
            ->assertRedirect();
    });

    it('retorna 403 para usuário comum', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/painel/estatisticas')
            ->assertForbidden();
    });

    it('admin consegue acessar a página com widgets e filtro de período', function () {
        NewsletterSubscriptionEvent::factory()->create([
            'action' => NewsletterEventAction::Subscribed->value,
            'source' => NewsletterEventSource::Popup->value,
            'popup_variant' => 'A',
        ]);

        $admin = createAdminUser();

        $this->actingAs($admin)
            ->get('/admin/painel/estatisticas')
            ->assertSuccessful()
            ->assertSee('Estatísticas do site', false)
            ->assertSee('Período', false)
            ->assertSee('Novos registos', false)
            ->assertSee('Novas inscrições na newsletter', false)
            ->assertSee('Teste A/B do popup', false)
            ->assertSee('Atualizar', false);
    });

    it('redireciona URL antiga newsletter-stats para estatisticas', function () {
        $admin = createAdminUser();

        $this->actingAs($admin)
            ->get('/admin/painel/newsletter-stats')
            ->assertRedirect('/admin/painel/estatisticas');
    });

});

describe('SiteMetrics com período', function () {

    it('filtra inscrições e registos pelo período de 7 dias', function () {
        $oldUser = User::factory()->create(['created_at' => now()->subDays(10)]);
        $newUser = User::factory()->create(['created_at' => now()->subDays(2)]);

        NewsletterSubscriptionEvent::factory()->create([
            'action' => NewsletterEventAction::Subscribed->value,
            'created_at' => now()->subDays(10),
        ]);
        NewsletterSubscriptionEvent::factory()->create([
            'action' => NewsletterEventAction::Subscribed->value,
            'created_at' => now()->subDays(1),
        ]);

        expect(SiteMetrics::newUserRegistrations('7'))->toBe(1)
            ->and(SiteMetrics::newSubscriptions('7'))->toBe(1);

        expect($oldUser)->not->toBeNull()
            ->and($newUser)->not->toBeNull();
    });

});
