<?php

use App\Ai\Agents\StatsAnalyst;
use App\Livewire\StatsAiChat;
use App\Models\SiteSetting;
use Livewire\Livewire;

beforeEach(function () {
    SiteSetting::set('ai_chat_model', 'anthropic/claude-3.5-sonnet');
});

it('a página de estatísticas renderiza com o chat embutido para o admin', function () {
    $response = $this->actingAs(createAdminUser())->get('/admin/painel/estatisticas');

    expect($response->getStatusCode())->toBeIn([200, 302]);
});

it('inicializa o período a partir do parâmetro de mount', function () {
    Livewire::test(StatsAiChat::class, ['period' => '7'])
        ->assertSet('period', '7');
});

it('cai para 30 dias se o período for inválido', function () {
    Livewire::test(StatsAiChat::class, ['period' => '999'])
        ->assertSet('period', '30');
});

it('envia a pergunta e adiciona pergunta e resposta ao histórico', function () {
    StatsAnalyst::fake(['As inscrições cresceram no período.']);

    Livewire::test(StatsAiChat::class)
        ->set('input', 'Como vão as inscrições?')
        ->call('send')
        ->assertSet('input', '')
        ->assertCount('messages', 2)
        ->assertSet('messages.0.role', 'user')
        ->assertSet('messages.0.content', 'Como vão as inscrições?')
        ->assertSet('messages.1.role', 'assistant')
        ->assertSet('messages.1.content', 'As inscrições cresceram no período.');

    StatsAnalyst::assertPrompted('Como vão as inscrições?');
});

it('ignora envio de pergunta vazia', function () {
    StatsAnalyst::fake(['nunca chamado']);

    Livewire::test(StatsAiChat::class)
        ->set('input', '   ')
        ->call('send')
        ->assertCount('messages', 0);

    StatsAnalyst::assertNotPrompted(fn () => true);
});

it('o botão de avaliação dispara um prompt com o período selecionado', function () {
    StatsAnalyst::fake(['Avaliação concluída.']);

    Livewire::test(StatsAiChat::class, ['period' => '7'])
        ->call('evaluateOnScreen')
        ->assertCount('messages', 2)
        ->assertSet('messages.1.content', 'Avaliação concluída.');

    StatsAnalyst::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'Últimos 7 dias'));
});

it('mostra erro e não chama o modelo quando não há modelo configurado', function () {
    SiteSetting::set('ai_chat_model', '');

    Livewire::test(StatsAiChat::class)
        ->set('input', 'Olá')
        ->call('send')
        ->assertCount('messages', 0)
        ->assertSet('error', fn ($error) => filled($error));
});

it('limpa a conversa', function () {
    StatsAnalyst::fake(['resposta']);

    Livewire::test(StatsAiChat::class)
        ->set('input', 'pergunta')
        ->call('send')
        ->assertCount('messages', 2)
        ->call('clearConversation')
        ->assertCount('messages', 0)
        ->assertSet('error', null);
});
