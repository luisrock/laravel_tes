<?php

use App\Filament\Pages\TemasElegiveis;
use App\Jobs\AnalisarAcordaoJob;
use App\Models\AiModel;
use App\Models\TeseAnalysisJob;
use App\Models\TeseAnalysisSection;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

beforeEach(function () {
    temasElegiveisSetupTables();
    DB::table('tese_analysis_jobs')->delete();
    DB::table('tese_analysis_sections')->delete();
    DB::table('tese_acordaos')->delete();
});

function temasElegiveisSetupTables(): void
{
    Schema::dropIfExists('stf_teses');
    Schema::dropIfExists('stj_teses');

    Schema::create('stf_teses', function ($table) {
        $table->id();
        $table->unsignedInteger('numero');
        $table->text('tema_texto')->nullable();
        $table->text('tese_texto')->nullable();
        $table->string('situacao')->nullable();
    });

    Schema::create('stj_teses', function ($table) {
        $table->id();
        $table->unsignedInteger('numero');
        $table->text('tema')->nullable();
        $table->text('tese_texto')->nullable();
        $table->string('situacao')->nullable();
    });
}

function temasElegiveisFakeOpenRouter(): void
{
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
}

function temasElegiveisCreateStfTese(int $numero = 1069, ?string $situacao = 'Trânsito em Julgado'): int
{
    return (int) DB::table('stf_teses')->insertGetId([
        'numero' => $numero,
        'tema_texto' => "Tema {$numero} para testes",
        'tese_texto' => 'Texto da tese.',
        'situacao' => $situacao,
    ]);
}

function temasElegiveisCreateAiModel(): AiModel
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

function temasElegiveisCreateAcordao(int $teseId, string $tribunal = 'STF'): void
{
    DB::table('tese_acordaos')->insert([
        'tese_id' => $teseId,
        'tribunal' => $tribunal,
        'numero_acordao' => 'RE 123',
        'tipo' => 'Principal',
        'label' => 'Principal',
        's3_key' => "acordaos/{$tribunal}/{$teseId}/abc.pdf",
        'filename_original' => 'acordao.pdf',
        'file_size' => 1024,
        'mime_type' => 'application/pdf',
        'checksum' => md5("{$tribunal}-{$teseId}"),
        'version' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

describe('TemasElegiveis — acesso', function () {

    it('redireciona visitante não autenticado', function () {
        $this->get('/admin/painel/temas-elegiveis')->assertRedirect();
    });

    it('retorna 403 para usuário comum', function () {
        $this->actingAs(User::factory()->create())
            ->get('/admin/painel/temas-elegiveis')
            ->assertForbidden();
    });

    it('permite acesso ao admin', function () {
        $response = $this->actingAs(createAdminUser())->get('/admin/painel/temas-elegiveis');

        expect($response->getStatusCode())->toBeIn([200, 302]);
    });

});

describe('TemasElegiveis — enfileiramento', function () {

    it('enfileira tema elegível e despacha o job', function () {
        Queue::fake();
        temasElegiveisFakeOpenRouter();

        $teseId = temasElegiveisCreateStfTese();
        temasElegiveisCreateAcordao($teseId);
        $key = "STF:{$teseId}";

        Livewire::actingAs(createAdminUser())
            ->test(TemasElegiveis::class)
            ->callAction(TestAction::make('analyze')->table($key))
            ->assertNotified();

        $job = TeseAnalysisJob::query()
            ->where('tese_id', $teseId)
            ->where('tribunal', 'STF')
            ->first();

        expect($job)->not->toBeNull()
            ->and($job->status)->toBe('queued')
            ->and($job->section_type)->toBe('all');

        Queue::assertPushed(AnalisarAcordaoJob::class, fn (AnalisarAcordaoJob $queued): bool => $queued->teseAnalysisJobId === $job->id);
    });

    it('força reprocesso mesmo com seções existentes', function () {
        Queue::fake();
        temasElegiveisFakeOpenRouter();

        $teseId = temasElegiveisCreateStfTese();
        temasElegiveisCreateAcordao($teseId);

        $aiModel = temasElegiveisCreateAiModel();
        TeseAnalysisSection::create([
            'tese_id' => $teseId,
            'tribunal' => 'STF',
            'section_type' => 'teaser',
            'content' => str_repeat('x', 250),
            'status' => 'published',
            'is_active' => false,
            'ai_model_id' => $aiModel->id,
            'generated_at' => now(),
        ]);

        $key = "STF:{$teseId}";

        Livewire::actingAs(createAdminUser())
            ->test(TemasElegiveis::class)
            ->callAction(TestAction::make('force')->table($key))
            ->assertNotified();

        expect(TeseAnalysisJob::query()->where('tese_id', $teseId)->where('status', 'queued')->exists())->toBeTrue();
        Queue::assertPushed(AnalisarAcordaoJob::class);
    });

    it('enfileira em lote apenas temas elegíveis da seleção', function () {
        Queue::fake();
        temasElegiveisFakeOpenRouter();

        $eligibleId = temasElegiveisCreateStfTese(1001);
        temasElegiveisCreateAcordao($eligibleId);

        $withIaId = temasElegiveisCreateStfTese(1002);
        temasElegiveisCreateAcordao($withIaId);
        $aiModel = temasElegiveisCreateAiModel();
        TeseAnalysisSection::create([
            'tese_id' => $withIaId,
            'tribunal' => 'STF',
            'section_type' => 'teaser',
            'content' => str_repeat('y', 250),
            'status' => 'draft',
            'is_active' => false,
            'ai_model_id' => $aiModel->id,
            'generated_at' => now(),
        ]);

        Livewire::actingAs(createAdminUser())
            ->test(TemasElegiveis::class)
            ->selectTableRecords(["STF:{$eligibleId}", "STF:{$withIaId}"])
            ->callAction(TestAction::make('enqueueEligible')->table()->bulk())
            ->assertNotified();

        expect(TeseAnalysisJob::query()->where('status', 'queued')->count())->toBe(1)
            ->and(TeseAnalysisJob::query()->where('tese_id', $eligibleId)->exists())->toBeTrue()
            ->and(TeseAnalysisJob::query()->where('tese_id', $withIaId)->exists())->toBeFalse();

        Queue::assertPushed(AnalisarAcordaoJob::class, 1);
    });

    it('remove jobs queued no lote retirar da fila', function () {
        $teseId = temasElegiveisCreateStfTese(2001);
        temasElegiveisCreateAcordao($teseId);

        $aiModel = temasElegiveisCreateAiModel();
        TeseAnalysisJob::create([
            'tese_id' => $teseId,
            'tribunal' => 'STF',
            'section_type' => 'all',
            'ai_model_id' => $aiModel->id,
            'status' => 'queued',
        ]);

        Livewire::actingAs(createAdminUser())
            ->test(TemasElegiveis::class)
            ->selectTableRecords(["STF:{$teseId}"])
            ->callAction(TestAction::make('dequeue')->table()->bulk())
            ->assertNotified();

        expect(TeseAnalysisJob::query()->where('tese_id', $teseId)->count())->toBe(0);
    });

});
