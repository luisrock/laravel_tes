<?php

use App\Models\SiteSetting;
use App\Models\User;

describe('Acesso à página NewsletterIntegrationSettings', function () {

    it('redireciona visitante não autenticado', function () {
        $this->get('/admin/painel/newsletter-integration-settings')
            ->assertRedirect();
    });

    it('retorna 403 para usuário comum', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/painel/newsletter-integration-settings')
            ->assertForbidden();
    });

    it('admin consegue acessar a página', function () {
        $admin = createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/painel/newsletter-integration-settings');

        expect($response->getStatusCode())->toBeIn([200, 302]);
    });

});

describe('Salvamento da flag newsletter', function () {

    it('salva newsletter_integration_enabled como 1 ou 0', function () {
        SiteSetting::set('newsletter_integration_enabled', '0');
        SiteSetting::set('newsletter_integration_enabled', '1');
        expect(SiteSetting::getAsBool('newsletter_integration_enabled', false))->toBeTrue();

        SiteSetting::set('newsletter_integration_enabled', '0');
        expect(SiteSetting::getAsBool('newsletter_integration_enabled', true))->toBeFalse();
    });

});
