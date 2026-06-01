<?php

use App\Jobs\AnalisarAcordaoJob;
use App\Models\AiModel;
use App\Models\SiteSetting;
use App\Models\TeseAnalysisJob;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config()->set('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
    config()->set('services.openrouter.management_key', 'mgmt-key');
    config()->set('ai.acordao_analysis.default_model', 'anthropic/claude-sonnet-4.6');
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

    SiteSetting::set('acordao_analysis_model', 'anthropic/claude-sonnet-4.6');

    DB::table('tese_analysis_jobs')->delete();
    DB::table('ai_models')->delete();
});

function enqueueAiTestModel(): AiModel
{
    return AiModel::create([
        'provider' => 'openrouter',
        'name' => 'Claude Sonnet 4.6',
        'model_id' => 'anthropic/claude-sonnet-4.6',
        'price_input_per_million' => 3.0,
        'price_output_per_million' => 15.0,
        'is_active' => true,
    ]);
}

it('retorna 403 para usuário não autenticado', function () {
    $this->post(route('tese.enqueue_ai', ['tribunal' => 'STF', 'tese_id' => 1]))
        ->assertRedirect(route('login'));
});

it('retorna 403 para usuário comum autenticado', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tese.enqueue_ai', ['tribunal' => 'STF', 'tese_id' => 1]))
        ->assertForbidden();
});

it('admin cria job na fila e despacha AnalisarAcordaoJob', function () {
    Queue::fake();

    $this->actingAs(createAdminUser())
        ->post(route('tese.enqueue_ai', ['tribunal' => 'STF', 'tese_id' => 999]))
        ->assertRedirect();

    $job = TeseAnalysisJob::query()
        ->where('tese_id', 999)
        ->where('tribunal', 'STF')
        ->first();

    expect($job)->not->toBeNull()
        ->and($job->status)->toBe('queued')
        ->and($job->section_type)->toBe('all');

    app()->terminate();

    Queue::assertPushed(AnalisarAcordaoJob::class);
});

it('admin não cria job duplicado se já existe job ativo', function () {
    Queue::fake();

    $model = enqueueAiTestModel();

    $existing = TeseAnalysisJob::create([
        'tese_id' => 999,
        'tribunal' => 'STF',
        'section_type' => 'all',
        'ai_model_id' => $model->id,
        'status' => 'queued',
    ]);

    $this->actingAs(createAdminUser())
        ->post(route('tese.enqueue_ai', ['tribunal' => 'STF', 'tese_id' => 999]))
        ->assertRedirect();

    expect(TeseAnalysisJob::query()->where('tese_id', 999)->where('tribunal', 'STF')->count())->toBe(1)
        ->and(TeseAnalysisJob::query()->find($existing->id)?->status)->toBe('queued');

    app()->terminate();

    Queue::assertNothingPushed();
});

it('admin re-enfileira job com erro via AcordaoAnalysisEnqueueService', function () {
    Queue::fake();

    $model = enqueueAiTestModel();

    TeseAnalysisJob::create([
        'tese_id' => 999,
        'tribunal' => 'STF',
        'section_type' => 'all',
        'ai_model_id' => $model->id,
        'status' => 'error',
        'attempts' => 3,
        'last_error' => 'Timeout',
    ]);

    $this->actingAs(createAdminUser())
        ->post(route('tese.enqueue_ai', ['tribunal' => 'STF', 'tese_id' => 999]))
        ->assertRedirect();

    $job = TeseAnalysisJob::query()->where('tese_id', 999)->where('tribunal', 'STF')->first();

    expect($job->status)->toBe('queued')
        ->and($job->attempts)->toBe(0)
        ->and($job->last_error)->toBeNull()
        ->and(TeseAnalysisJob::query()->where('tese_id', 999)->count())->toBe(1);

    app()->terminate();

    Queue::assertPushed(AnalisarAcordaoJob::class);
});

it('retorna 422 se não há modelo de IA configurado', function () {
    SiteSetting::set('acordao_analysis_model', '');
    config()->set('ai.acordao_analysis.default_model', '');

    $this->actingAs(createAdminUser())
        ->post(route('tese.enqueue_ai', ['tribunal' => 'STF', 'tese_id' => 999]))
        ->assertStatus(422);
});

it('salva tribunal em maiúsculas', function () {
    Queue::fake();

    $this->actingAs(createAdminUser())
        ->post(route('tese.enqueue_ai', ['tribunal' => 'stf', 'tese_id' => 999]))
        ->assertRedirect();

    expect(TeseAnalysisJob::query()->where('tese_id', 999)->where('tribunal', 'STF')->exists())->toBeTrue();
});

it('retorna 422 para tribunal sem análise de acórdãos', function () {
    $this->actingAs(createAdminUser())
        ->post(route('tese.enqueue_ai', ['tribunal' => 'TST', 'tese_id' => 999]))
        ->assertStatus(422);
});
