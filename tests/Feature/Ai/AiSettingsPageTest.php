<?php

use App\Filament\Pages\AiSettings;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

/**
 * Simula o catálogo do OpenRouter contendo o modelo informado, para que o Select aceite o valor.
 */
function fakeOpenRouterCatalogue(string $modelId): void
{
    config()->set('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
    config()->set('services.openrouter.management_key', 'mgmt-key');
    Cache::forget('openrouter:models');

    Cache::forget('openrouter:models:raw');

    Http::fake([
        'openrouter.ai/api/v1/models' => Http::response([
            'data' => [[
                'id' => $modelId,
                'name' => $modelId,
                'pricing' => ['prompt' => '0.000003', 'completion' => '0.000015'],
                'architecture' => [
                    'input_modalities' => ['text', 'image', 'file'],
                    'output_modalities' => ['text'],
                ],
            ]],
        ]),
        'openrouter.ai/api/v1/credits' => Http::response([
            'data' => ['total_credits' => 10.0, 'total_usage' => 1.0],
        ]),
    ]);
}

describe('AiSettings — acesso', function () {

    it('redireciona visitante não autenticado', function () {
        $this->get('/admin/painel/configuracoes-ia')->assertRedirect();
    });

    it('retorna 403 para usuário comum', function () {
        $this->actingAs(User::factory()->create())
            ->get('/admin/painel/configuracoes-ia')
            ->assertForbidden();
    });

    it('permite acesso ao admin', function () {
        $admin = createAdminUser();

        $response = $this->actingAs($admin)->get('/admin/painel/configuracoes-ia');

        expect($response->getStatusCode())->toBeIn([200, 302]);
    });

});

describe('AiSettings — persistência do modelo', function () {

    it('grava ai_chat_model via save da página', function () {
        $admin = createAdminUser();
        fakeOpenRouterCatalogue('anthropic/claude-3.5-sonnet');

        Livewire::actingAs($admin)
            ->test(AiSettings::class)
            ->set('data.ai_chat_model', 'anthropic/claude-3.5-sonnet')
            ->call('save')
            ->assertHasNoErrors();

        expect(SiteSetting::get('ai_chat_model'))->toBe('anthropic/claude-3.5-sonnet');
    });

    it('carrega o modelo atual no mount', function () {
        $admin = createAdminUser();
        SiteSetting::set('ai_chat_model', 'openai/gpt-4o');

        Livewire::actingAs($admin)
            ->test(AiSettings::class)
            ->assertSet('data.ai_chat_model', 'openai/gpt-4o');
    });

});

describe('AiSettings — modelo de análise de acórdãos', function () {

    it('grava acordao_analysis_model via save da página', function () {
        $admin = createAdminUser();
        fakeOpenRouterCatalogue('anthropic/claude-sonnet-4.6');

        Livewire::actingAs($admin)
            ->test(AiSettings::class)
            ->set('data.acordao_analysis_model', 'anthropic/claude-sonnet-4.6')
            ->call('save')
            ->assertHasNoErrors();

        expect(SiteSetting::get('acordao_analysis_model'))->toBe('anthropic/claude-sonnet-4.6');
    });

    it('usa o default da config no mount quando não há setting', function () {
        $admin = createAdminUser();

        Livewire::actingAs($admin)
            ->test(AiSettings::class)
            ->assertSet('data.acordao_analysis_model', 'anthropic/claude-sonnet-4.6');
    });

    it('carrega o modelo de acórdãos gravado no mount', function () {
        $admin = createAdminUser();
        SiteSetting::set('acordao_analysis_model', 'google/gemini-2.5-pro');

        Livewire::actingAs($admin)
            ->test(AiSettings::class)
            ->assertSet('data.acordao_analysis_model', 'google/gemini-2.5-pro');
    });

});
