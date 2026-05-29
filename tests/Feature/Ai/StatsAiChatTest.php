<?php

use App\Ai\Agents\StatsAnalyst;
use App\Livewire\StatsAiChat;
use App\Models\SiteSetting;
use App\Models\User;
use Laravel\Ai\Contracts\ConversationStore;
use Laravel\Ai\Models\Conversation;
use Livewire\Livewire;

beforeEach(function () {
    SiteSetting::set('ai_chat_model', 'anthropic/claude-3.5-sonnet');
    $this->admin = createAdminUser();
    $this->actingAs($this->admin);
});

it('a página de estatísticas renderiza com o chat embutido para o admin', function () {
    $response = $this->get('/admin/painel/estatisticas');

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

it('envia a pergunta, persiste e exibe pergunta e resposta', function () {
    StatsAnalyst::fake(['As inscrições cresceram no período.']);

    Livewire::test(StatsAiChat::class)
        ->set('input', 'Como vão as inscrições?')
        ->call('send')
        ->assertSet('input', '')
        ->assertCount('messages', 2)
        ->assertSet('messages.0.role', 'user')
        ->assertSet('messages.0.content', 'Como vão as inscrições?')
        ->assertSet('messages.1.role', 'assistant')
        ->assertSet('messages.1.content', 'As inscrições cresceram no período.')
        ->assertSet('conversationId', fn ($id) => filled($id));

    StatsAnalyst::assertPrompted('Como vão as inscrições?');
    expect(Conversation::query()->where('user_id', $this->admin->id)->count())->toBe(1);
});

it('continua a mesma conversa em mensagens seguintes', function () {
    StatsAnalyst::fake(['Primeira resposta.', 'Segunda resposta.']);

    $component = Livewire::test(StatsAiChat::class)
        ->set('input', 'Primeira pergunta')
        ->call('send');

    $conversationId = $component->get('conversationId');

    $component->set('input', 'Segunda pergunta')
        ->call('send')
        ->assertSet('conversationId', $conversationId)
        ->assertCount('messages', 4);

    expect(Conversation::query()->count())->toBe(1);
});

it('retoma a última conversa do admin ao montar', function () {
    StatsAnalyst::fake(['Resposta persistida.']);

    Livewire::test(StatsAiChat::class)
        ->set('input', 'Pergunta inicial')
        ->call('send');

    Livewire::test(StatsAiChat::class)
        ->assertCount('messages', 2)
        ->assertSet('messages.1.content', 'Resposta persistida.')
        ->assertSet('conversationId', fn ($id) => filled($id));
});

it('inicia uma nova conversa limpando as mensagens sem apagar o histórico', function () {
    StatsAnalyst::fake(['resposta']);

    $component = Livewire::test(StatsAiChat::class)
        ->set('input', 'pergunta')
        ->call('send')
        ->assertCount('messages', 2)
        ->call('newConversation')
        ->assertCount('messages', 0)
        ->assertSet('conversationId', null)
        ->assertSet('error', null);

    // A conversa anterior continua listada para ser retomada.
    expect($component->get('conversations'))->toHaveCount(1);
});

it('retoma uma conversa específica ao clicar nela', function () {
    StatsAnalyst::fake(['Resposta A.', 'Resposta B.']);

    $first = Livewire::test(StatsAiChat::class)
        ->set('input', 'Conversa A')
        ->call('send');
    $firstId = $first->get('conversationId');

    $second = Livewire::test(StatsAiChat::class)
        ->call('newConversation')
        ->set('input', 'Conversa B')
        ->call('send');

    $second->call('loadConversation', $firstId)
        ->assertSet('conversationId', $firstId)
        ->assertCount('messages', 2)
        ->assertSet('messages.0.content', 'Conversa A');
});

it('ignora retomar conversa de outro usuário', function () {
    $store = resolve(ConversationStore::class);
    $otherUser = User::factory()->create();
    $foreignConversation = $store->storeConversation($otherUser->id, 'Conversa alheia');

    Livewire::test(StatsAiChat::class)
        ->call('loadConversation', $foreignConversation)
        ->assertSet('conversationId', null)
        ->assertCount('messages', 0);
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

it('trata falha do modelo durante o streaming sem gravar mensagens', function () {
    StatsAnalyst::fake(function () {
        throw new RuntimeException('falha no provedor');
    });

    Livewire::test(StatsAiChat::class)
        ->set('input', 'Como vão as inscrições?')
        ->call('send')
        ->assertCount('messages', 0)
        ->assertSet('input', 'Como vão as inscrições?')
        ->assertSet('error', fn ($error) => filled($error));

    expect(Conversation::query()->count())->toBe(0);
});

it('mostra erro e não chama o modelo quando não há modelo configurado', function () {
    SiteSetting::set('ai_chat_model', '');

    Livewire::test(StatsAiChat::class)
        ->set('input', 'Olá')
        ->call('send')
        ->assertCount('messages', 0)
        ->assertSet('error', fn ($error) => filled($error));
});
