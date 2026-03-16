<?php

use App\Models\AiModel;
use App\Models\TeseAnalysisJob;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('tese_analysis_jobs')->delete();
    DB::table('ai_models')->delete();
});

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

it('admin cria job na fila ao clicar em resumir com IA', function () {
    $admin = createAdminUser();
    $model = AiModel::create([
        'provider' => 'openai',
        'name' => 'GPT-4o Test',
        'model_id' => 'gpt-4o',
        'price_input_per_million' => 5.0,
        'price_output_per_million' => 15.0,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('tese.enqueue_ai', ['tribunal' => 'STF', 'tese_id' => 999]))
        ->assertRedirect();

    expect(TeseAnalysisJob::where('tese_id', 999)->where('tribunal', 'STF')->where('status', 'queued')->exists())->toBeTrue();
});

it('admin não cria job duplicado se já existe job ativo', function () {
    $admin = createAdminUser();
    $model = AiModel::create([
        'provider' => 'openai',
        'name' => 'GPT-4o Test',
        'model_id' => 'gpt-4o',
        'price_input_per_million' => 5.0,
        'price_output_per_million' => 15.0,
        'is_active' => true,
    ]);

    TeseAnalysisJob::create([
        'tese_id' => 999,
        'tribunal' => 'STF',
        'section_type' => 'all',
        'ai_model_id' => $model->id,
        'status' => 'queued',
    ]);

    $this->actingAs($admin)
        ->post(route('tese.enqueue_ai', ['tribunal' => 'STF', 'tese_id' => 999]))
        ->assertRedirect();

    expect(TeseAnalysisJob::where('tese_id', 999)->where('tribunal', 'STF')->count())->toBe(1);
});

it('admin re-enfileira job com erro via updateOrCreate', function () {
    $admin = createAdminUser();
    $model = AiModel::create([
        'provider' => 'openai',
        'name' => 'GPT-4o Test',
        'model_id' => 'gpt-4o',
        'price_input_per_million' => 5.0,
        'price_output_per_million' => 15.0,
        'is_active' => true,
    ]);

    TeseAnalysisJob::create([
        'tese_id' => 999,
        'tribunal' => 'STF',
        'section_type' => 'all',
        'ai_model_id' => $model->id,
        'status' => 'error',
        'attempts' => 3,
        'last_error' => 'Timeout',
    ]);

    $this->actingAs($admin)
        ->post(route('tese.enqueue_ai', ['tribunal' => 'STF', 'tese_id' => 999]))
        ->assertRedirect();

    $job = TeseAnalysisJob::where('tese_id', 999)->where('tribunal', 'STF')->first();
    expect($job->status)->toBe('queued');
    expect($job->attempts)->toBe(0);
    expect($job->last_error)->toBeNull();
    expect(TeseAnalysisJob::where('tese_id', 999)->count())->toBe(1);
});

it('retorna 422 se não há modelo de IA ativo', function () {
    $admin = createAdminUser();

    $this->actingAs($admin)
        ->post(route('tese.enqueue_ai', ['tribunal' => 'STF', 'tese_id' => 999]))
        ->assertStatus(422);
});

it('salva tribunal em maiúsculas', function () {
    $admin = createAdminUser();
    AiModel::create([
        'provider' => 'openai',
        'name' => 'GPT-4o Test',
        'model_id' => 'gpt-4o',
        'price_input_per_million' => 5.0,
        'price_output_per_million' => 15.0,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->post(route('tese.enqueue_ai', ['tribunal' => 'stf', 'tese_id' => 999]))
        ->assertRedirect();

    expect(TeseAnalysisJob::where('tese_id', 999)->where('tribunal', 'STF')->exists())->toBeTrue();
});
