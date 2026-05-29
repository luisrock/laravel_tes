<?php

use App\Ai\Agents\StatsAnalyst;
use App\Ai\Tools\QuerySiteMetrics;
use App\Models\SiteSetting;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Messages\MessageRole;

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

it('maps ephemeral history into AI SDK messages', function () {
    $agent = new StatsAnalyst([
        ['role' => 'user', 'content' => 'Olá'],
        ['role' => 'assistant', 'content' => 'Oi, como posso ajudar?'],
    ]);

    $messages = collect($agent->messages());

    expect($messages)->toHaveCount(2)
        ->and($messages[0])->toBeInstanceOf(Message::class)
        ->and($messages[0]->role)->toBe(MessageRole::User)
        ->and($messages[0]->content)->toBe('Olá')
        ->and($messages[1]->role)->toBe(MessageRole::Assistant);
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
