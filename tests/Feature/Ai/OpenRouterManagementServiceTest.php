<?php

use App\Services\Ai\OpenRouterManagementService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
    config()->set('services.openrouter.management_key', 'mgmt-key');
    Cache::forget('openrouter:models');
});

function makeOpenRouterService(): OpenRouterManagementService
{
    return app(OpenRouterManagementService::class);
}

it('computes remaining credits as total minus usage', function () {
    Http::fake([
        'openrouter.ai/api/v1/credits' => Http::response([
            'data' => ['total_credits' => 100.0, 'total_usage' => 25.5],
        ]),
    ]);

    expect(makeOpenRouterService()->remainingCredits())->toBe(74.5);
});

it('returns null credits when the management key is missing', function () {
    config()->set('services.openrouter.management_key', null);

    expect(makeOpenRouterService()->remainingCredits())->toBeNull();
});

it('returns null credits when the API fails', function () {
    Http::fake([
        'openrouter.ai/api/v1/credits' => Http::response([], 500),
    ]);

    expect(makeOpenRouterService()->remainingCredits())->toBeNull();
});

it('parses and filters text models into select options', function () {
    Http::fake([
        'openrouter.ai/api/v1/models' => Http::response([
            'data' => [
                [
                    'id' => 'anthropic/claude-3.5-sonnet',
                    'name' => 'Claude 3.5 Sonnet',
                    'context_length' => 200000,
                    'pricing' => ['prompt' => '0.000003', 'completion' => '0.000015'],
                    'architecture' => ['output_modalities' => ['text']],
                ],
                [
                    'id' => 'openai/dall-e-3',
                    'name' => 'DALL-E 3',
                    'pricing' => ['prompt' => '0.00004'],
                    'architecture' => ['output_modalities' => ['image']],
                ],
            ],
        ]),
    ]);

    $models = makeOpenRouterService()->availableModels();

    expect($models)->toHaveKey('anthropic/claude-3.5-sonnet')
        ->and($models)->not->toHaveKey('openai/dall-e-3');

    $label = $models['anthropic/claude-3.5-sonnet'];

    expect($label)->toContain('Claude 3.5 Sonnet')
        ->toContain('$3/M in')
        ->toContain('$15/M out')
        ->toContain('200K');
});

it('caches the models catalogue', function () {
    Http::fake([
        'openrouter.ai/api/v1/models' => Http::response([
            'data' => [[
                'id' => 'x/y',
                'name' => 'XY',
                'pricing' => ['prompt' => '0.000001', 'completion' => '0.000002'],
                'architecture' => ['output_modalities' => ['text']],
            ]],
        ]),
    ]);

    $service = makeOpenRouterService();
    $service->availableModels();
    $service->availableModels();

    Http::assertSentCount(1);
});

it('returns empty models on API failure', function () {
    Http::fake([
        'openrouter.ai/api/v1/models' => Http::response([], 500),
    ]);

    expect(makeOpenRouterService()->availableModels())->toBe([]);
});
