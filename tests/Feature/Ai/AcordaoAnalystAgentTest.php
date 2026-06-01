<?php

use App\Ai\Agents\AcordaoAnalyst;
use App\Models\AiPrompt;
use App\Models\SiteSetting;

function fakeAcordaoStructuredResponse(): array
{
    return [
        'erro' => null,
        'teaser' => 'Tema 1069 do STJ: o tribunal fixou entendimento sobre tributação.',
        'caso_fatico' => str_repeat('Fatos do caso concreto. ', 40),
        'contornos_juridicos' => str_repeat('Fundamentos jurídicos aplicáveis. ', 50),
        'modulacao' => 'Não houve modulação de efeitos neste julgamento.',
        'tese_explicada' => str_repeat('Explicação didática da decisão. ', 50),
    ];
}

it('resolves the openrouter provider and the configured model', function () {
    SiteSetting::set('acordao_analysis_model', 'anthropic/claude-sonnet-4.6');

    $agent = new AcordaoAnalyst;

    expect($agent->provider())->toBe('openrouter')
        ->and($agent->model())->toBe('anthropic/claude-sonnet-4.6');
});

it('falls back to the config default when no site setting exists', function () {
    SiteSetting::clearCache('acordao_analysis_model');

    expect((new AcordaoAnalyst)->model())->toBe('anthropic/claude-sonnet-4.6');
});

it('allows overriding the model slug per instance', function () {
    SiteSetting::set('acordao_analysis_model', 'anthropic/claude-sonnet-4.6');

    expect((new AcordaoAnalyst('google/gemini-2.5-pro'))->model())->toBe('google/gemini-2.5-pro');
});

it('throws when no model can be resolved', function () {
    config()->set('ai.acordao_analysis.default_model', '');
    SiteSetting::set('acordao_analysis_model', '');

    expect(fn () => (new AcordaoAnalyst)->model())
        ->toThrow(RuntimeException::class);
});

it('reports configuration state via isConfigured', function () {
    config()->set('ai.acordao_analysis.default_model', '');
    SiteSetting::set('acordao_analysis_model', '');

    expect(AcordaoAnalyst::isConfigured())->toBeFalse();

    SiteSetting::set('acordao_analysis_model', 'anthropic/claude-sonnet-4.6');

    expect(AcordaoAnalyst::isConfigured())->toBeTrue();
});

it('reads instructions from the editable prompt', function () {
    AiPrompt::create([
        'key' => AcordaoAnalyst::SYSTEM_PROMPT_KEY,
        'title' => 'Custom',
        'content' => 'Prompt editado pelo admin',
        'description' => 'teste',
    ]);

    expect((string) (new AcordaoAnalyst)->instructions())->toBe('Prompt editado pelo admin');
});

it('falls back to default instructions when prompt is absent', function () {
    expect((string) (new AcordaoAnalyst)->instructions())
        ->toContain('RETORNE EXCLUSIVAMENTE um JSON')
        ->toContain('{texto_tema}');
});

it('uses a configurable HTTP timeout', function () {
    config()->set('services.openrouter.request_timeout', 180);

    expect((new AcordaoAnalyst)->timeout())->toBe(180);
});

it('returns structured output with all section keys when faked', function () {
    SiteSetting::set('acordao_analysis_model', 'anthropic/claude-sonnet-4.6');

    AcordaoAnalyst::fake([fakeAcordaoStructuredResponse()]);

    $response = AcordaoAnalyst::make()->prompt('Analise o Tema 1069 do STJ.');

    expect($response->structured)->toHaveKeys([
        'erro',
        'teaser',
        'caso_fatico',
        'contornos_juridicos',
        'modulacao',
        'tese_explicada',
    ])->and($response->structured['erro'])->toBeNull()
        ->and($response->structured['teaser'])->toContain('Tema 1069 do STJ');

    AcordaoAnalyst::assertPrompted('Analise o Tema 1069 do STJ.');
});

it('auto-generates structured fake data matching the schema', function () {
    SiteSetting::set('acordao_analysis_model', 'anthropic/claude-sonnet-4.6');

    AcordaoAnalyst::fake();

    $response = AcordaoAnalyst::make()->prompt('Analise o tema.');

    expect($response->structured)->toHaveKeys([
        'erro',
        'teaser',
        'caso_fatico',
        'contornos_juridicos',
        'modulacao',
        'tese_explicada',
    ]);
});
