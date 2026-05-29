<?php

use App\Ai\Agents\StatsAnalyst;
use App\Filament\Resources\AiPromptResource\Pages\CreateAiPrompt;
use App\Filament\Resources\AiPromptResource\Pages\EditAiPrompt;
use App\Models\AiPrompt;
use App\Models\User;
use Livewire\Livewire;

describe('AiPromptResource — acesso', function () {

    it('redireciona visitante não autenticado', function () {
        $this->get('/admin/painel/ai-prompts')->assertRedirect();
    });

    it('retorna 403 para usuário comum', function () {
        $this->actingAs(User::factory()->create())
            ->get('/admin/painel/ai-prompts')
            ->assertForbidden();
    });

    it('permite acesso ao admin', function () {
        $response = $this->actingAs(createAdminUser())->get('/admin/painel/ai-prompts');

        expect($response->getStatusCode())->toBeIn([200, 302]);
    });

});

describe('AiPromptResource — CRUD', function () {

    it('cria um prompt pela página do Filament', function () {
        Livewire::actingAs(createAdminUser())
            ->test(CreateAiPrompt::class)
            ->fillForm([
                'key' => StatsAnalyst::SYSTEM_PROMPT_KEY,
                'title' => 'Analista de Estatísticas',
                'content' => 'Conteúdo do prompt.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(AiPrompt::contentForKey(StatsAnalyst::SYSTEM_PROMPT_KEY))->toBe('Conteúdo do prompt.');
    });

    it('edita o conteúdo de um prompt existente', function () {
        $prompt = AiPrompt::factory()->create([
            'key' => StatsAnalyst::SYSTEM_PROMPT_KEY,
            'content' => 'Antigo.',
        ]);

        Livewire::actingAs(createAdminUser())
            ->test(EditAiPrompt::class, ['record' => $prompt->getRouteKey()])
            ->fillForm(['content' => 'Novo conteúdo.'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect(AiPrompt::contentForKey(StatsAnalyst::SYSTEM_PROMPT_KEY))->toBe('Novo conteúdo.');
    });

    it('mantém a key inalterável na edição (campo desabilitado)', function () {
        $prompt = AiPrompt::factory()->create(['key' => StatsAnalyst::SYSTEM_PROMPT_KEY]);

        Livewire::actingAs(createAdminUser())
            ->test(EditAiPrompt::class, ['record' => $prompt->getRouteKey()])
            ->assertFormFieldIsDisabled('key');
    });

});
