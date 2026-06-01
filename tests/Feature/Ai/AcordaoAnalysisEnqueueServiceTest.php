<?php

use App\Jobs\AnalisarAcordaoJob;
use App\Models\AiModel;
use App\Models\SiteSetting;
use App\Models\TeseAnalysisJob;
use App\Models\TeseAnalysisSection;
use App\Services\Ai\AcordaoAnalysisEnqueueService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config()->set('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
    config()->set('services.openrouter.management_key', 'mgmt-key');
    Cache::forget('openrouter:models');
    Cache::forget('openrouter:models:raw');

    Http::fake([
        'openrouter.ai/api/v1/models' => Http::response([
            'data' => [[
                'id' => 'anthropic/claude-sonnet-4.6',
                'name' => 'Claude Sonnet 4.6',
                'pricing' => ['prompt' => '0.000003', 'completion' => '0.000015'],
                'architecture' => [
                    'input_modalities' => ['text', 'image', 'file'],
                    'output_modalities' => ['text'],
                ],
            ]],
        ]),
    ]);

    DB::table('tese_analysis_jobs')->delete();
    DB::table('tese_analysis_sections')->delete();
});

it('resolve modelo via SiteSetting e AiModelResolver', function () {
    SiteSetting::set('acordao_analysis_model', 'anthropic/claude-sonnet-4.6');

    $model = app(AcordaoAnalysisEnqueueService::class)->resolveConfiguredModel();

    expect($model->provider)->toBe('openrouter')
        ->and($model->model_id)->toBe('anthropic/claude-sonnet-4.6');
});

it('não enfileira quando já há job ativo', function () {
    Queue::fake();

    $aiModel = AiModel::create([
        'provider' => 'openrouter',
        'name' => 'Test',
        'model_id' => 'anthropic/claude-sonnet-4.6',
        'price_input_per_million' => 3.0,
        'price_output_per_million' => 15.0,
        'is_active' => true,
    ]);

    TeseAnalysisJob::create([
        'tese_id' => 10,
        'tribunal' => 'STF',
        'section_type' => 'all',
        'ai_model_id' => $aiModel->id,
        'status' => 'running',
    ]);

    $result = app(AcordaoAnalysisEnqueueService::class)->enqueue(10, 'STF');

    expect($result)->toBeNull();
    Queue::assertNothingPushed();
});

it('força re-enfileiramento resetando job com erro', function () {
    Queue::fake();

    $aiModel = AiModel::create([
        'provider' => 'openrouter',
        'name' => 'Test',
        'model_id' => 'anthropic/claude-sonnet-4.6',
        'price_input_per_million' => 3.0,
        'price_output_per_million' => 15.0,
        'is_active' => true,
    ]);

    TeseAnalysisJob::create([
        'tese_id' => 10,
        'tribunal' => 'STF',
        'section_type' => 'all',
        'ai_model_id' => $aiModel->id,
        'status' => 'error',
        'attempts' => 3,
        'last_error' => 'Timeout',
    ]);

    $job = app(AcordaoAnalysisEnqueueService::class)->enqueue(10, 'STF', force: true);

    expect($job->status)->toBe('queued')
        ->and($job->attempts)->toBe(0)
        ->and($job->last_error)->toBeNull();

    app()->terminate();

    Queue::assertPushed(AnalisarAcordaoJob::class);
});

it('remove job em qualquer status', function () {
    $aiModel = AiModel::create([
        'provider' => 'openrouter',
        'name' => 'Test',
        'model_id' => 'anthropic/claude-sonnet-4.6',
        'price_input_per_million' => 3.0,
        'price_output_per_million' => 15.0,
        'is_active' => true,
    ]);

    TeseAnalysisJob::create([
        'tese_id' => 10,
        'tribunal' => 'STF',
        'section_type' => 'all',
        'ai_model_id' => $aiModel->id,
        'status' => 'running',
    ]);

    expect(app(AcordaoAnalysisEnqueueService::class)->removeJob(10, 'STF'))->toBeTrue()
        ->and(TeseAnalysisJob::query()->where('tese_id', 10)->count())->toBe(0);
});

it('permite enfileirar após job em error sem forçar', function () {
    Queue::fake();

    $aiModel = AiModel::create([
        'provider' => 'openrouter',
        'name' => 'Test',
        'model_id' => 'anthropic/claude-sonnet-4.6',
        'price_input_per_million' => 3.0,
        'price_output_per_million' => 15.0,
        'is_active' => true,
    ]);

    TeseAnalysisJob::create([
        'tese_id' => 10,
        'tribunal' => 'STF',
        'section_type' => 'all',
        'ai_model_id' => $aiModel->id,
        'status' => 'error',
        'last_error' => 'OpenRouter Error: [400] Provider returned error',
    ]);

    $job = app(AcordaoAnalysisEnqueueService::class)->enqueue(10, 'STF');

    expect($job)->not->toBeNull()
        ->and($job->status)->toBe('queued');
});

it('considera inelegível quando há seções de IA', function () {
    $aiModel = AiModel::create([
        'provider' => 'openrouter',
        'name' => 'Test',
        'model_id' => 'anthropic/claude-sonnet-4.6',
        'price_input_per_million' => 3.0,
        'price_output_per_million' => 15.0,
        'is_active' => true,
    ]);

    TeseAnalysisSection::create([
        'tese_id' => 10,
        'tribunal' => 'STF',
        'section_type' => 'teaser',
        'content' => str_repeat('a', 250),
        'status' => 'draft',
        'is_active' => false,
        'ai_model_id' => $aiModel->id,
        'generated_at' => now(),
    ]);

    expect(app(AcordaoAnalysisEnqueueService::class)->isEligible(10, 'STF'))->toBeFalse();
});
