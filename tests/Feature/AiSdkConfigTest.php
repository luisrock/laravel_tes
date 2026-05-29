<?php

use Laravel\Ai\Enums\Lab;

it('registers the openrouter provider for the AI SDK', function () {
    $provider = config('ai.providers.openrouter');

    expect($provider)->toBeArray()
        ->and($provider['driver'])->toBe('openrouter')
        ->and($provider)->toHaveKey('key');
});

it('exposes the native OpenRouter lab enum case', function () {
    expect(Lab::OpenRouter->value)->toBe('openrouter');
});

it('resolves the openrouter management service config', function () {
    config()->set('services.openrouter.management_key', 'mgmt-key');
    config()->set('services.openrouter.key', 'model-key');

    expect(config('services.openrouter.base_url'))->toBe('https://openrouter.ai/api/v1')
        ->and(config('services.openrouter.management_key'))->toBe('mgmt-key')
        ->and(config('services.openrouter.key'))->toBe('model-key');
});

it('registers the AI SDK conversation migration for persistent conversations', function () {
    $migrations = glob(database_path('migrations/*agent_conversations*'));

    expect($migrations)->not->toBeEmpty();
});
