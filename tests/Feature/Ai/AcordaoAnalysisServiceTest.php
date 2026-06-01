<?php

use App\Ai\Agents\AcordaoAnalyst;
use App\Exceptions\AcordaoAnalysisPermanentException;
use App\Models\AiModel;
use App\Models\TeseAcordao;
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
});

function analysisQaPad(string $prefix, int $minLength): string
{
    $content = $prefix;

    while (mb_strlen($content) < $minLength) {
        $content .= 'x';
    }

    return $content;
}

function createStfTese(int $numero = 1069): int
{
    return (int) DB::table('stf_teses')->insertGetId([
        'numero' => $numero,
        'tema_texto' => "Tema {$numero} — matéria tributária",
        'tese_texto' => 'Texto da tese firmada para testes.',
        'situacao' => 'Trânsito em Julgado',
    ]);
}

function createOpenRouterAiModel(): AiModel
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

function createAcordao(int $teseId, string $checksum, string $tipo = 'Principal'): TeseAcordao
{
    $s3Key = "acordaos/stf/{$teseId}/{$checksum}.pdf";
    Storage::disk('s3')->put($s3Key, '%PDF-1.4 fake content');

    return TeseAcordao::create([
        'tese_id' => $teseId,
        'tribunal' => 'STF',
        'numero_acordao' => 'RE 123456',
        'tipo' => $tipo,
        'label' => 'Principal',
        's3_key' => $s3Key,
        'filename_original' => 'acordao.pdf',
        'file_size' => 1024,
        'mime_type' => 'application/pdf',
        'checksum' => $checksum,
        'version' => 1,
        'uploaded_by' => null,
    ]);
}

function fakeAcordaoAgent(array $structured, int $inputTokens = 5000, int $outputTokens = 2500): void
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

function fakeStructuredAnalysis(array $overrides = []): array
{
    return array_merge([
        'erro' => null,
        'teaser' => analysisQaPad('Tema 1069 do STF: entendimento sobre tributação e repercussão geral. ', 200),
        'caso_fatico' => analysisQaPad('Descrição objetiva dos fatos do caso concreto analisado pelo tribunal. ', 600),
        'contornos_juridicos' => analysisQaPad('Fundamentos jurídicos e ratio decidendi aplicados pelo STF no julgamento. ', 800),
        'modulacao' => 'Não houve modulação de efeitos neste julgamento.',
        'tese_explicada' => analysisQaPad('Explicação didática dos impactos práticos da tese para contribuintes. ', 800),
    ], $overrides);
}

function analyzeFixture(array $structuredOverrides = []): array
{
    $teseId = createStfTese();
    $aiModel = createOpenRouterAiModel();
    createAcordao($teseId, 'checksum-principal-aaa');

    fakeAcordaoAgent(fakeStructuredAnalysis($structuredOverrides));

    $service = app(AcordaoAnalysisService::class);
    $result = $service->analyze($teseId, 'STF', $aiModel);

    return [$teseId, $aiModel, $result];
}

it('generates five sections with teaser published and others draft', function () {
    [$teseId, $aiModel] = analyzeFixture();

    $sections = TeseAnalysisSection::query()
        ->where('tese_id', $teseId)
        ->where('tribunal', 'STF')
        ->where('ai_model_id', $aiModel->id)
        ->get()
        ->keyBy('section_type');

    expect($sections)->toHaveCount(5)
        ->and($sections['teaser']->status)->toBe('published')
        ->and($sections['caso_fatico']->status)->toBe('draft')
        ->and($sections['contornos_juridicos']->status)->toBe('draft')
        ->and($sections['modulacao']->status)->toBe('draft')
        ->and($sections['tese_explicada']->status)->toBe('draft');

    foreach ($sections as $section) {
        expect($section->is_active)->toBeFalse();
    }
});

it('persists tokens cost snapshots and raw usage', function () {
    [$teseId, $aiModel, $result] = analyzeFixture();

    expect($result->inputTokens)->toBeGreaterThan(0)
        ->and($result->outputTokens)->toBeGreaterThan(0)
        ->and($result->costUsd)->toBeGreaterThan(0.0);

    $section = TeseAnalysisSection::query()
        ->where('tese_id', $teseId)
        ->where('section_type', 'teaser')
        ->first();

    expect($section->tokens_input)->not->toBeNull()
        ->and($section->tokens_output)->not->toBeNull()
        ->and((float) $section->cost_usd)->toBeGreaterThan(0)
        ->and((float) $section->price_snapshot_input)->toBe(3.0)
        ->and((float) $section->price_snapshot_output)->toBe(15.0)
        ->and($section->raw_usage)->toBeArray()
        ->and($section->provider_request_id)->not->toBeNull();
});

it('skips generation when all sections already exist', function () {
    $teseId = createStfTese();
    $aiModel = createOpenRouterAiModel();
    createAcordao($teseId, 'checksum-principal-aaa');

    $promptCalls = 0;
    AcordaoAnalyst::fake(function () use (&$promptCalls) {
        $promptCalls++;

        return new StructuredAgentResponse(
            'inv-test-'.uniqid(),
            fakeStructuredAnalysis(),
            '{}',
            new Usage(5000, 2500),
            new Meta('openrouter', 'anthropic/claude-sonnet-4.6'),
        );
    });

    $service = app(AcordaoAnalysisService::class);

    $first = $service->analyze($teseId, 'STF', $aiModel);
    expect($first->skippedDueToIdempotency)->toBeFalse();

    $second = $service->analyze($teseId, 'STF', $aiModel);

    expect($second->skippedDueToIdempotency)->toBeTrue()
        ->and($second->sectionsCreated)->toBe(0)
        ->and($second->inputTokens)->toBe(0)
        ->and($second->costUsd)->toBe(0.0)
        ->and($promptCalls)->toBe(1);

    expect(TeseAnalysisSection::query()->where('tese_id', $teseId)->count())->toBe(5);
});

it('throws a permanent exception when the model reports erro', function () {
    $teseId = createStfTese();
    $aiModel = createOpenRouterAiModel();
    createAcordao($teseId, 'checksum-principal-aaa');

    fakeAcordaoAgent([
        'erro' => 'O acórdão não corresponde ao tema solicitado.',
        'teaser' => '',
        'caso_fatico' => '',
        'contornos_juridicos' => '',
        'modulacao' => '',
        'tese_explicada' => '',
    ]);

    app(AcordaoAnalysisService::class)->analyze($teseId, 'STF', $aiModel);
})->throws(AcordaoAnalysisPermanentException::class, 'Erro reportado pela IA');

it('skips empty sections in the json response', function () {
    [$teseId] = analyzeFixture(['modulacao' => '']);

    expect(TeseAnalysisSection::query()->where('tese_id', $teseId)->count())->toBe(4)
        ->and(TeseAnalysisSection::query()->where('section_type', 'modulacao')->exists())->toBeFalse();
});

it('throws when the pdf is missing from s3', function () {
    $teseId = createStfTese();
    $aiModel = createOpenRouterAiModel();

    TeseAcordao::create([
        'tese_id' => $teseId,
        'tribunal' => 'STF',
        'numero_acordao' => 'RE 123456',
        'tipo' => 'Principal',
        'label' => 'Principal',
        's3_key' => 'acordaos/inexistente.pdf',
        'filename_original' => 'acordao.pdf',
        'file_size' => 1024,
        'mime_type' => 'application/pdf',
        'checksum' => 'checksum-sem-arquivo',
        'version' => 1,
    ]);

    fakeAcordaoAgent(fakeStructuredAnalysis());

    app(AcordaoAnalysisService::class)->analyze($teseId, 'STF', $aiModel);
})->throws(AcordaoAnalysisPermanentException::class, 'PDF não encontrado no S3');

it('stores teaser as draft when qa fails', function () {
    [$teseId] = analyzeFixture([
        'teaser' => 'Teaser curto demais para publicar.',
    ]);

    $teaser = TeseAnalysisSection::query()
        ->where('tese_id', $teseId)
        ->where('section_type', 'teaser')
        ->first();

    expect($teaser->status)->toBe('draft');
});
