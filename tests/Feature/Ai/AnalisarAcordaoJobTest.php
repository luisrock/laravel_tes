<?php

use App\Ai\Agents\AcordaoAnalyst;
use App\Jobs\AnalisarAcordaoJob;
use App\Models\AiModel;
use App\Models\TeseAnalysisJob;
use App\Models\TeseAnalysisSection;
use App\Services\Ai\AcordaoAnalysisService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\StructuredAgentResponse;

beforeEach(function () {
    Schema::dropIfExists('stf_teses');
    Schema::create('stf_teses', function ($table) {
        $table->id();
        $table->unsignedInteger('numero');
        $table->text('tema_texto')->nullable();
        $table->text('tese_texto')->nullable();
        $table->string('situacao')->nullable();
    });

    Storage::fake('s3');
    DB::table('tese_analysis_jobs')->delete();
    DB::table('tese_analysis_sections')->delete();
});

function jobTestPad(string $prefix, int $minLength): string
{
    $content = $prefix;

    while (mb_strlen($content) < $minLength) {
        $content .= 'x';
    }

    return $content;
}

function jobTestCreateStfTese(int $numero = 1069): int
{
    return (int) DB::table('stf_teses')->insertGetId([
        'numero' => $numero,
        'tema_texto' => "Tema {$numero} — matéria tributária",
        'tese_texto' => 'Texto da tese firmada para testes.',
        'situacao' => 'Trânsito em Julgado',
    ]);
}

function jobTestCreateOpenRouterAiModel(): AiModel
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

function jobTestCreateAcordao(int $teseId, string $checksum): void
{
    $s3Key = "acordaos/stf/{$teseId}/{$checksum}.pdf";
    Storage::disk('s3')->put($s3Key, '%PDF-1.4 fake content');

    \App\Models\TeseAcordao::create([
        'tese_id' => $teseId,
        'tribunal' => 'STF',
        'numero_acordao' => 'RE 123456',
        'tipo' => 'Principal',
        'label' => 'Principal',
        's3_key' => $s3Key,
        'filename_original' => 'acordao.pdf',
        'file_size' => 1024,
        'mime_type' => 'application/pdf',
        'checksum' => $checksum,
        'version' => 1,
    ]);
}

function jobTestFakeStructuredAnalysis(): array
{
    return [
        'erro' => null,
        'teaser' => jobTestPad('Tema 1069 do STF: entendimento sobre tributação e repercussão geral. ', 200),
        'caso_fatico' => jobTestPad('Descrição objetiva dos fatos do caso concreto analisado pelo tribunal. ', 600),
        'contornos_juridicos' => jobTestPad('Fundamentos jurídicos e ratio decidendi aplicados pelo STF no julgamento. ', 800),
        'modulacao' => 'Não houve modulação de efeitos neste julgamento.',
        'tese_explicada' => jobTestPad('Explicação didática dos impactos práticos da tese para contribuintes. ', 800),
    ];
}

function jobTestFakeAcordaoAgent(array $structured, int $inputTokens = 5000, int $outputTokens = 2500): void
{
    AcordaoAnalyst::fake(function () use ($structured, $inputTokens, $outputTokens) {
        return new StructuredAgentResponse(
            'inv-test-'.uniqid(),
            $structured,
            json_encode($structured),
            new Usage($inputTokens, $outputTokens),
            new Meta('openrouter', 'anthropic/claude-sonnet-4.6'),
        );
    });
}

function jobTestCreateQueuedJob(int $teseId, AiModel $aiModel, array $overrides = []): TeseAnalysisJob
{
    return TeseAnalysisJob::create(array_merge([
        'tese_id' => $teseId,
        'tribunal' => 'STF',
        'section_type' => 'all',
        'ai_model_id' => $aiModel->id,
        'status' => 'queued',
        'attempts' => 0,
        'max_attempts' => 3,
    ], $overrides));
}

function jobTestDispatch(TeseAnalysisJob $job): void
{
    AnalisarAcordaoJob::dispatchSync($job->id);
}

it('marks the job as done with usage metrics on success', function () {
    $teseId = jobTestCreateStfTese();
    $aiModel = jobTestCreateOpenRouterAiModel();
    jobTestCreateAcordao($teseId, 'checksum-principal-aaa');
    jobTestFakeAcordaoAgent(jobTestFakeStructuredAnalysis());

    $job = jobTestCreateQueuedJob($teseId, $aiModel);

    jobTestDispatch($job);

    $job->refresh();

    expect($job->status)->toBe('done')
        ->and($job->attempts)->toBe(1)
        ->and($job->locked_by)->toBeNull()
        ->and($job->started_at)->not->toBeNull()
        ->and($job->completed_at)->not->toBeNull()
        ->and($job->input_tokens)->toBeGreaterThan(0)
        ->and($job->output_tokens)->toBeGreaterThan(0)
        ->and((float) $job->cost_usd)->toBeGreaterThan(0.0)
        ->and($job->last_error)->toBeNull();

    expect(TeseAnalysisSection::query()->where('tese_id', $teseId)->count())->toBe(5);
});

it('marks the job as error without retry on permanent failures', function () {
    $teseId = jobTestCreateStfTese();
    $aiModel = jobTestCreateOpenRouterAiModel();
    jobTestCreateAcordao($teseId, 'checksum-principal-aaa');

    jobTestFakeAcordaoAgent([
        'erro' => 'O acórdão não corresponde ao tema solicitado.',
        'teaser' => '',
        'caso_fatico' => '',
        'contornos_juridicos' => '',
        'modulacao' => '',
        'tese_explicada' => '',
    ]);

    $job = jobTestCreateQueuedJob($teseId, $aiModel);

    jobTestDispatch($job);

    $job->refresh();

    expect($job->status)->toBe('error')
        ->and($job->attempts)->toBe(1)
        ->and($job->last_error)->toContain('Erro reportado pela IA')
        ->and($job->completed_at)->not->toBeNull()
        ->and($job->locked_by)->toBeNull();

    expect(TeseAnalysisSection::query()->where('tese_id', $teseId)->count())->toBe(0);
});

it('requeues retryable failures until max attempts are exhausted', function () {
    $teseId = jobTestCreateStfTese();
    $aiModel = jobTestCreateOpenRouterAiModel();

    $this->mock(AcordaoAnalysisService::class, function ($mock): void {
        $mock->shouldReceive('analyze')
            ->once()
            ->andThrow(new RuntimeException('Timeout de rede'));
    });

    $job = jobTestCreateQueuedJob($teseId, $aiModel, ['max_attempts' => 3]);

    jobTestDispatch($job);

    $job->refresh();

    expect($job->status)->toBe('queued')
        ->and($job->attempts)->toBe(1)
        ->and($job->last_error)->toBe('Timeout de rede')
        ->and($job->locked_by)->toBeNull()
        ->and($job->completed_at)->toBeNull();
});

it('marks the job as error when retryable failures exceed max attempts', function () {
    $teseId = jobTestCreateStfTese();
    $aiModel = jobTestCreateOpenRouterAiModel();

    $this->mock(AcordaoAnalysisService::class, function ($mock): void {
        $mock->shouldReceive('analyze')
            ->once()
            ->andThrow(new RuntimeException('Timeout de rede'));
    });

    $job = jobTestCreateQueuedJob($teseId, $aiModel, ['max_attempts' => 1]);

    jobTestDispatch($job);

    $job->refresh();

    expect($job->status)->toBe('error')
        ->and($job->attempts)->toBe(1)
        ->and($job->last_error)->toBe('Timeout de rede')
        ->and($job->completed_at)->not->toBeNull();
});

it('marks the job as done without cost when all sections already exist', function () {
    $teseId = jobTestCreateStfTese();
    $aiModel = jobTestCreateOpenRouterAiModel();
    jobTestCreateAcordao($teseId, 'checksum-principal-aaa');

    $promptCalls = 0;
    AcordaoAnalyst::fake(function () use (&$promptCalls) {
        $promptCalls++;

        return new StructuredAgentResponse(
            'inv-test-'.uniqid(),
            jobTestFakeStructuredAnalysis(),
            '{}',
            new Usage(5000, 2500),
            new Meta('openrouter', 'anthropic/claude-sonnet-4.6'),
        );
    });

    app(AcordaoAnalysisService::class)->analyze($teseId, 'STF', $aiModel);

    $job = jobTestCreateQueuedJob($teseId, $aiModel);

    jobTestDispatch($job);

    $job->refresh();

    expect($job->status)->toBe('done')
        ->and($job->input_tokens)->toBe(0)
        ->and($job->output_tokens)->toBe(0)
        ->and((float) $job->cost_usd)->toBe(0.0)
        ->and($promptCalls)->toBe(1);

    expect(TeseAnalysisSection::query()->where('tese_id', $teseId)->count())->toBe(5);
});

it('ignores jobs that are not queued', function () {
    $teseId = jobTestCreateStfTese();
    $aiModel = jobTestCreateOpenRouterAiModel();

    $job = jobTestCreateQueuedJob($teseId, $aiModel, ['status' => 'running']);

    $this->mock(AcordaoAnalysisService::class, function ($mock): void {
        $mock->shouldNotReceive('analyze');
    });

    jobTestDispatch($job);

    expect($job->fresh()->status)->toBe('running');
});
