<?php

use App\Ai\Agents\StatsAnalyst;
use App\Ai\Tools\QuerySiteMetrics;
use App\Models\SiteSetting;
use Illuminate\Support\Str;

it('resolves the openrouter provider and the configured model', function () {
    SiteSetting::set('ai_chat_model', 'anthropic/claude-3.5-sonnet');

    $agent = new StatsAnalyst;

    expect($agent->provider())->toBe('openrouter')
        ->and($agent->model())->toBe('anthropic/claude-3.5-sonnet');
});

it('throws when no model is configured', function () {
    SiteSetting::set('ai_chat_model', '');

    expect(fn () => (new StatsAnalyst)->model())
        ->toThrow(RuntimeException::class);
});

it('reports configuration state via isConfigured', function () {
    SiteSetting::set('ai_chat_model', '');
    expect(StatsAnalyst::isConfigured())->toBeFalse();

    SiteSetting::set('ai_chat_model', 'openai/gpt-4o');
    expect(StatsAnalyst::isConfigured())->toBeTrue();
});

it('não tem participante nem mensagens quando criado sem conversa', function () {
    $agent = new StatsAnalyst;

    expect($agent->hasConversationParticipant())->toBeFalse()
        ->and($agent->currentConversation())->toBeNull()
        ->and(collect($agent->messages())->all())->toBe([]);
});

it('define o participante ao iniciar uma conversa para um usuário', function () {
    $user = createAdminUser();

    $agent = (new StatsAnalyst)->forUser($user);

    expect($agent->hasConversationParticipant())->toBeTrue()
        ->and($agent->conversationParticipant()->id)->toBe($user->id)
        ->and($agent->currentConversation())->toBeNull();
});

it('carrega o histórico do ConversationStore ao retomar uma conversa', function () {
    $user = createAdminUser();

    $store = resolve(Laravel\Ai\Contracts\ConversationStore::class);
    $conversationId = $store->storeConversation($user->id, 'Inscrições');

    Laravel\Ai\Models\ConversationMessage::query()->create([
        'id' => (string) Str::uuid7(),
        'conversation_id' => $conversationId,
        'user_id' => $user->id,
        'agent' => StatsAnalyst::class,
        'role' => 'user',
        'content' => 'Como vão as inscrições?',
        'attachments' => [], 'tool_calls' => [], 'tool_results' => [], 'usage' => [], 'meta' => [],
    ]);

    $agent = (new StatsAnalyst)->continue($conversationId, $user);

    $messages = collect($agent->messages());

    expect($messages)->toHaveCount(1)
        ->and($messages->first()->content)->toBe('Como vão as inscrições?')
        ->and($agent->currentConversation())->toBe($conversationId);
});

it('exposes the QuerySiteMetrics tool', function () {
    expect((new StatsAnalyst)->tools())->toContainOnlyInstancesOf(QuerySiteMetrics::class);
});

it('uses a configurable HTTP timeout and bounded tool steps', function () {
    config()->set('services.openrouter.request_timeout', 150);

    expect((new StatsAnalyst)->timeout())->toBe(150)
        ->and((new StatsAnalyst)->maxSteps())->toBe(6);
});

it('returns the model response when prompted (faked gateway)', function () {
    SiteSetting::set('ai_chat_model', 'anthropic/claude-3.5-sonnet');

    StatsAnalyst::fake(['As inscrições cresceram 12% no período.']);

    $response = (new StatsAnalyst)->prompt('Como evoluíram as inscrições?');

    expect((string) $response)->toBe('As inscrições cresceram 12% no período.');

    StatsAnalyst::assertPrompted('Como evoluíram as inscrições?');
});
