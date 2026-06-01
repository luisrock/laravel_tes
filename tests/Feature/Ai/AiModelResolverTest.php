<?php

use App\Models\AiModel;
use App\Services\Ai\AiModelResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
    config()->set('services.openrouter.management_key', 'mgmt-key');
    Cache::forget('openrouter:models');
    Cache::forget('openrouter:models:raw');
});

function fakeCatalogPricing(string $prompt, string $completion, string $name = 'Anthropic: Claude Sonnet 4.6'): void
{
    Http::fake([
        'openrouter.ai/api/v1/models' => Http::response([
            'data' => [[
                'id' => 'anthropic/claude-sonnet-4.6',
                'name' => $name,
                'pricing' => ['prompt' => $prompt, 'completion' => $completion],
                'architecture' => [
                    'input_modalities' => ['text', 'image', 'file'],
                    'output_modalities' => ['text'],
                ],
            ]],
        ]),
    ]);
}

function resolver(): AiModelResolver
{
    return app(AiModelResolver::class);
}

it('creates a new ai_models row from the catalogue', function () {
    fakeCatalogPricing('0.000003', '0.000015');

    $model = resolver()->resolveOpenRouterModel('anthropic/claude-sonnet-4.6');

    expect($model->exists)->toBeTrue()
        ->and($model->provider)->toBe('openrouter')
        ->and($model->model_id)->toBe('anthropic/claude-sonnet-4.6')
        ->and($model->name)->toBe('Anthropic: Claude Sonnet 4.6')
        ->and((float) $model->price_input_per_million)->toBe(3.0)
        ->and((float) $model->price_output_per_million)->toBe(15.0)
        ->and($model->is_active)->toBeTrue();

    expect(AiModel::query()->where('provider', 'openrouter')->count())->toBe(1);
});

it('reuses an existing row without duplicating', function () {
    fakeCatalogPricing('0.000003', '0.000015');

    $first = resolver()->resolveOpenRouterModel('anthropic/claude-sonnet-4.6');
    $second = resolver()->resolveOpenRouterModel('anthropic/claude-sonnet-4.6');

    expect($second->id)->toBe($first->id);
    expect(AiModel::query()->where('provider', 'openrouter')->count())->toBe(1);
});

it('updates name and prices when the catalogue changes', function () {
    $payload = fn (string $prompt, string $completion, string $name): array => [
        'data' => [[
            'id' => 'anthropic/claude-sonnet-4.6',
            'name' => $name,
            'pricing' => ['prompt' => $prompt, 'completion' => $completion],
            'architecture' => [
                'input_modalities' => ['text', 'image', 'file'],
                'output_modalities' => ['text'],
            ],
        ]],
    ];

    Http::fakeSequence('openrouter.ai/api/v1/models')
        ->push($payload('0.000003', '0.000015', 'Old Name'))
        ->push($payload('0.000004', '0.000020', 'New Name'));

    $created = resolver()->resolveOpenRouterModel('anthropic/claude-sonnet-4.6');

    Cache::forget('openrouter:models:raw');

    $updated = resolver()->resolveOpenRouterModel('anthropic/claude-sonnet-4.6');

    expect($updated->id)->toBe($created->id)
        ->and($updated->name)->toBe('New Name')
        ->and((float) $updated->price_input_per_million)->toBe(4.0)
        ->and((float) $updated->price_output_per_million)->toBe(20.0);

    expect(AiModel::query()->where('provider', 'openrouter')->count())->toBe(1);
});

it('preserves existing prices and manual deactivation when the catalogue is unavailable', function () {
    $existing = AiModel::create([
        'provider' => 'openrouter',
        'model_id' => 'anthropic/claude-sonnet-4.6',
        'name' => 'Já cadastrado',
        'price_input_per_million' => 3.0,
        'price_output_per_million' => 15.0,
        'is_active' => false,
    ]);

    Http::fake([
        'openrouter.ai/api/v1/models' => Http::response([], 500),
    ]);

    $resolved = resolver()->resolveOpenRouterModel('anthropic/claude-sonnet-4.6');

    expect($resolved->id)->toBe($existing->id)
        ->and($resolved->name)->toBe('Já cadastrado')
        ->and((float) $resolved->price_input_per_million)->toBe(3.0)
        ->and((float) $resolved->price_output_per_million)->toBe(15.0)
        ->and($resolved->is_active)->toBeFalse();
});
