<?php

use App\Models\AiModel;
use Illuminate\Support\Facades\DB;
use Tests\MySQLTestCase;

function extractArticleSchema(string $html): ?array
{
    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);

    foreach ($matches[1] ?? [] as $scriptContent) {
        if (! str_contains($scriptContent, '"@type": "Article"') && ! str_contains($scriptContent, '"@type":"Article"')) {
            continue;
        }

        return json_decode($scriptContent, true, 512, JSON_THROW_ON_ERROR);
    }

    return null;
}

$FIXTURE_NUMERO = 30123;

beforeEach(function () use ($FIXTURE_NUMERO) {
    if (MySQLTestCase::integrationDatabaseUnavailable()) {
        return;
    }

    DB::table('tese_analysis_sections')->where('tese_id', $FIXTURE_NUMERO)->delete();
    DB::table('stf_teses')->where('numero', $FIXTURE_NUMERO)->delete();
    DB::table('stj_teses')->where('numero', $FIXTURE_NUMERO)->delete();
    DB::table('ai_models')->delete();

    DB::table('stf_teses')->insert([
        'id' => $FIXTURE_NUMERO,
        'numero' => $FIXTURE_NUMERO,
        'tema_texto' => '[FIXTURE] Tema STF com análise IA',
        'tese_texto' => '[FIXTURE] Tese STF com análise IA',
        'situacao' => 'Ativo',
        'relator' => '[FIXTURE]',
        'aprovadaEm' => '2024-06-01',
    ]);

    DB::table('stj_teses')->insert([
        'id' => $FIXTURE_NUMERO,
        'numero' => $FIXTURE_NUMERO,
        'orgao' => '[FIXTURE]',
        'tema' => '[FIXTURE] Tema STJ com análise IA',
        'tese_texto' => '[FIXTURE] Tese STJ com análise IA',
        'ramos' => 'Direito Civil',
        'atualizadaEm' => '2024-07-15',
        'situacao' => 'Ativo',
    ]);

    AiModel::create([
        'provider' => 'openai',
        'name' => 'GPT-4o Test',
        'model_id' => 'gpt-4o',
        'price_input_per_million' => 5.0,
        'price_output_per_million' => 15.0,
        'is_active' => true,
    ]);
});

afterEach(function () use ($FIXTURE_NUMERO) {
    if (MySQLTestCase::integrationDatabaseUnavailable()) {
        return;
    }

    DB::table('tese_analysis_sections')->where('tese_id', $FIXTURE_NUMERO)->delete();
    DB::table('stf_teses')->where('numero', $FIXTURE_NUMERO)->delete();
    DB::table('stj_teses')->where('numero', $FIXTURE_NUMERO)->delete();
    DB::table('ai_models')->delete();
});

it('renderiza article json ld com paywall para tese stf com conteudo ia', function () use ($FIXTURE_NUMERO) {
    config(['subscription.enabled' => false]);

    $aiModelId = DB::table('ai_models')->value('id');

    DB::table('tese_analysis_sections')->insert([
        [
            'tese_id' => $FIXTURE_NUMERO,
            'tribunal' => 'STF',
            'section_type' => 'teaser',
            'content' => 'Resumo inicial liberado.',
            'status' => 'published',
            'is_active' => true,
            'ai_model_id' => $aiModelId,
            'generated_at' => '2026-03-18 10:00:00',
        ],
        [
            'tese_id' => $FIXTURE_NUMERO,
            'tribunal' => 'STF',
            'section_type' => 'caso_fatico',
            'content' => 'Conteúdo completo protegido.',
            'status' => 'published',
            'is_active' => true,
            'ai_model_id' => $aiModelId,
            'generated_at' => '2026-03-19 12:30:00',
        ],
    ]);

    $response = $this->get("/tese/stf/{$FIXTURE_NUMERO}")
        ->assertSuccessful();

    $schema = extractArticleSchema($response->getContent());

    expect($schema)->not->toBeNull();
    expect($schema['@type'])->toBe('Article');
    expect($schema['isAccessibleForFree'])->toBeFalse();
    expect($schema['hasPart'][0]['cssSelector'])->toBe('.premium-content-blur');
    expect($schema['hasPart'][0]['isAccessibleForFree'])->toBeFalse();
    expect($schema['datePublished'])->toStartWith('2024-06-01');
    expect($schema['dateModified'])->toStartWith('2026-03-19T12:30:00');
});

it('renderiza article json ld com a data de publicacao correta para stj', function () use ($FIXTURE_NUMERO) {
    config(['subscription.enabled' => false]);

    $aiModelId = DB::table('ai_models')->value('id');

    DB::table('tese_analysis_sections')->insert([
        'tese_id' => $FIXTURE_NUMERO,
        'tribunal' => 'STJ',
        'section_type' => 'teaser',
        'content' => 'Resumo inicial liberado.',
        'status' => 'published',
        'is_active' => true,
        'ai_model_id' => $aiModelId,
        'generated_at' => '2026-03-19 09:15:00',
    ]);

    $response = $this->get("/tese/stj/{$FIXTURE_NUMERO}")
        ->assertSuccessful();

    $schema = extractArticleSchema($response->getContent());

    expect($schema)->not->toBeNull();
    expect($schema['datePublished'])->toStartWith('2024-07-15');
    expect($schema['dateModified'])->toStartWith('2026-03-19T09:15:00');
});

it('nao renderiza article json ld quando a tese nao possui conteudo ia', function () use ($FIXTURE_NUMERO) {
    config(['subscription.enabled' => false]);

    $response = $this->get("/tese/stf/{$FIXTURE_NUMERO}")
        ->assertSuccessful();

    expect(extractArticleSchema($response->getContent()))->toBeNull();
});
