<?php

use App\Services\SearchDatabaseService;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Fixture numbers — altos o suficiente para não colidir com dados reais.
| SMALLINT max = 32767; MAX(numero) real: stj_sumulas=676, stj_teses=1377.
|--------------------------------------------------------------------------
*/
$FIXTURE_NUMEROS = [30001, 30002];

beforeEach(function () use ($FIXTURE_NUMEROS) {
    // Garantir limpeza de eventuais resíduos de execuções anteriores
    DB::table('stj_sumulas')->whereIn('numero', $FIXTURE_NUMEROS)->delete();
    DB::table('stj_teses')->whereIn('numero', $FIXTURE_NUMEROS)->delete();

    // Súmulas:
    //   30001 — contém "indenizacao moral consumidor"
    //   30002 — contém "indenizacao acidente trabalho"
    DB::table('stj_sumulas')->insert([
        [
            'numero' => 30001,
            'titulo' => '[FIXTURE] Indenização por dano moral ao consumidor',
            'texto' => '[FIXTURE]',
            'texto_raw' => 'indenizacao moral consumidor',
            'ramos' => 'Direito Civil',
            'isCancelada' => 0,
        ],
        [
            'numero' => 30002,
            'titulo' => '[FIXTURE] Indenização por acidente de trabalho',
            'texto' => '[FIXTURE]',
            'texto_raw' => 'indenizacao acidente trabalho',
            'ramos' => 'Trabalhista',
            'isCancelada' => 0,
        ],
    ]);

    // Teses:
    //   30001 — contém "indenizacao moral consumidor"
    //   30002 — contém "indenizacao acidente trabalho"
    DB::table('stj_teses')->insert([
        [
            'numero' => 30001,
            'orgao' => 'Teste-Fixture',
            'tema' => 'responsabilidade moral consumidor',
            'tese_texto' => 'indenizacao moral consumidor',
            'ramos' => 'Direito Civil',
            'atualizadaEm' => '2024-01-01',
            'situacao' => 'Ativo',
        ],
        [
            'numero' => 30002,
            'orgao' => 'Teste-Fixture',
            'tema' => 'acidente trabalho',
            'tese_texto' => 'indenizacao acidente trabalho',
            'ramos' => 'Trabalhista',
            'atualizadaEm' => '2024-01-01',
            'situacao' => 'Ativo',
        ],
    ]);

    // InnoDB FULLTEXT mantém novos registros em cache de inserção até que o
    // índice seja reconstruído. OPTIMIZE TABLE força o flush imediato do cache,
    // garantindo que os fixtures sejam encontrados por MATCH AGAINST.
    // DB::select() faz fetchAll() internamente, consumindo o result set do OPTIMIZE TABLE
    // e evitando "Cannot execute queries while other unbuffered queries are active"
    DB::select('OPTIMIZE TABLE stj_sumulas');
    DB::select('OPTIMIZE TABLE stj_teses');
});

afterEach(function () use ($FIXTURE_NUMEROS) {
    DB::table('stj_sumulas')->whereIn('numero', $FIXTURE_NUMEROS)->delete();
    DB::table('stj_teses')->whereIn('numero', $FIXTURE_NUMEROS)->delete();
});

// ---------------------------------------------------------------------------
// BLOCO 1 — Queries FULLTEXT BOOLEAN MODE diretas (sem camada de serviço)
// Valida que os conectores booleanos funcionam como esperado no MySQL real.
// ---------------------------------------------------------------------------

it('FULLTEXT boolean mode: AND implícito (+a +b) retorna somente a linha com ambos os termos', function () {
    $rows = DB::table('stj_sumulas')
        ->whereRaw('MATCH (texto_raw, ramos) AGAINST (? IN BOOLEAN MODE)', ['+indenizacao +moral'])
        ->orderBy('numero')
        ->pluck('numero')
        ->toArray();

    expect($rows)->toContain(30001)
        ->and($rows)->not->toContain(30002);
});

it('FULLTEXT boolean mode: NOT (-) exclui linhas que contêm o termo proibido', function () {
    $rows = DB::table('stj_sumulas')
        ->whereRaw('MATCH (texto_raw, ramos) AGAINST (? IN BOOLEAN MODE)', ['+indenizacao -moral'])
        ->orderBy('numero')
        ->pluck('numero')
        ->toArray();

    expect($rows)->toContain(30002)
        ->and($rows)->not->toContain(30001);
});

it('FULLTEXT boolean mode: OR (sem sinal) retorna linhas com qualquer um dos termos', function () {
    $rows = DB::table('stj_sumulas')
        ->whereRaw('MATCH (texto_raw, ramos) AGAINST (? IN BOOLEAN MODE)', ['moral trabalho'])
        ->orderBy('numero')
        ->pluck('numero')
        ->toArray();

    expect($rows)->toContain(30001)
        ->and($rows)->toContain(30002);
});

it('FULLTEXT boolean mode: frase entre aspas exige adjacência dos termos', function () {
    $rows = DB::table('stj_sumulas')
        ->whereRaw('MATCH (texto_raw, ramos) AGAINST (? IN BOOLEAN MODE)', ['+"indenizacao moral"'])
        ->orderBy('numero')
        ->pluck('numero')
        ->toArray();

    // "indenizacao moral" estão adjacentes em 30001 mas não em 30002
    expect($rows)->toContain(30001)
        ->and($rows)->not->toContain(30002);
});

// ---------------------------------------------------------------------------
// BLOCO 2 — Parser → string final → MySQL
// Valida que buildBooleanSearchString() gera strings que o MySQL processa
// corretamente, fechando a cadeia parser ↔ motor.
// ---------------------------------------------------------------------------

it('parser AND implícito gera +a +b e MySQL aplica corretamente', function () {
    $svc = app(SearchDatabaseService::class);
    $finalString = $svc->buildBooleanSearchString('indenizacao moral');

    expect($finalString)->toBe('+indenizacao +moral');

    $rows = DB::table('stj_sumulas')
        ->whereRaw('MATCH (texto_raw, ramos) AGAINST (? IN BOOLEAN MODE)', [$finalString])
        ->orderBy('numero')
        ->pluck('numero')
        ->toArray();

    expect($rows)->toContain(30001)
        ->and($rows)->not->toContain(30002);
});

it('parser OR gera string sem sinais e MySQL retorna qualquer linha com qualquer termo', function () {
    $svc = app(SearchDatabaseService::class);
    $finalString = $svc->buildBooleanSearchString('moral OU trabalho');

    expect($finalString)->toBe('moral trabalho');

    $rows = DB::table('stj_sumulas')
        ->whereRaw('MATCH (texto_raw, ramos) AGAINST (? IN BOOLEAN MODE)', [$finalString])
        ->orderBy('numero')
        ->pluck('numero')
        ->toArray();

    expect($rows)->toContain(30001)
        ->and($rows)->toContain(30002);
});

it('parser NOT gera +a -b e MySQL exclui o termo proibido corretamente', function () {
    $svc = app(SearchDatabaseService::class);
    $finalString = $svc->buildBooleanSearchString('indenizacao não moral');

    expect($finalString)->toBe('+indenizacao -moral');

    $rows = DB::table('stj_sumulas')
        ->whereRaw('MATCH (texto_raw, ramos) AGAINST (? IN BOOLEAN MODE)', [$finalString])
        ->orderBy('numero')
        ->pluck('numero')
        ->toArray();

    expect($rows)->toContain(30002)
        ->and($rows)->not->toContain(30001);
});

it('parser de frase entre aspas gera "..." e MySQL exige adjacência', function () {
    $svc = app(SearchDatabaseService::class);
    $finalString = $svc->buildBooleanSearchString('"indenizacao moral"');

    // Quando a frase é o único token, o parser não injeta '+' (comportamento correto).
    // MySQL BOOLEAN MODE respeita adjacência com aspas mesmo sem sinal obrigatório.
    expect($finalString)->toBe('"indenizacao moral"');

    $rows = DB::table('stj_sumulas')
        ->whereRaw('MATCH (texto_raw, ramos) AGAINST (? IN BOOLEAN MODE)', [$finalString])
        ->orderBy('numero')
        ->pluck('numero')
        ->toArray();

    expect($rows)->toContain(30001)
        ->and($rows)->not->toContain(30002);
});

it('termo curto não recebe sinal mas não impede que o termo longo retorne resultados', function () {
    $svc = app(SearchDatabaseService::class);
    // "de indenizacao": "de" < 3 chars → sem sinal; "indenizacao" → +indenizacao
    $finalString = $svc->buildBooleanSearchString('de indenizacao');

    // SMALLINT 30001 e 30002 ambos têm "indenizacao" → os dois devem aparecer
    $rows = DB::table('stj_sumulas')
        ->whereRaw('MATCH (texto_raw, ramos) AGAINST (? IN BOOLEAN MODE)', [$finalString])
        ->orderBy('numero')
        ->pluck('numero')
        ->toArray();

    expect($rows)->toContain(30001)
        ->and($rows)->toContain(30002);
});

// ---------------------------------------------------------------------------
// BLOCO 3 — SearchDatabaseService::countBooleanModeMatches contra MySQL real
// ---------------------------------------------------------------------------

it('countBooleanModeMatches conta corretamente no MySQL real', function () {
    $svc = app(SearchDatabaseService::class);

    $count = $svc->countBooleanModeMatches('indenizacao moral', 'stj_teses', 'tese_texto,tema,ramos');

    // Pelo menos a fixture 30001 deve ser contada (tem "indenizacao" e "moral")
    expect($count)->toBeGreaterThanOrEqual(1);
});

it('countBooleanModeMatches retorna zero para termo inexistente', function () {
    $svc = app(SearchDatabaseService::class);

    $count = $svc->countBooleanModeMatches('xyzzy_termo_inexistente_abc', 'stj_teses', 'tese_texto,tema,ramos');

    expect($count)->toBe(0);
});

// ---------------------------------------------------------------------------
// BLOCO 4 — SearchDatabaseService::executeResult end-to-end com MySQL real
// Valida o shape de retorno sem cache, passando pelo executeResult() completo.
// ---------------------------------------------------------------------------

it('executeResult retorna totais corretos para STJ com MySQL real', function () {
    $tribunalConfig = config('tes_constants.lista_tribunais.STJ');
    $svc = app(SearchDatabaseService::class);

    $result = $svc->executeResult('indenizacao moral', 'stj', $tribunalConfig);

    expect($result->sumula()->total())->toBeGreaterThanOrEqual(1)
        ->and($result->tese()->total())->toBeGreaterThanOrEqual(1)
        ->and($result->totalCount())->toBeGreaterThanOrEqual(2);
});

it('executeResult inclui fixture de súmula no shape de hits com MySQL real', function () {
    $tribunalConfig = config('tes_constants.lista_tribunais.STJ');
    $svc = app(SearchDatabaseService::class);

    $result = $svc->executeResult('indenizacao moral', 'stj', $tribunalConfig);

    $sumulaNums = array_column($result->sumula()->hits(), 'trib_sum_numero');
    expect($sumulaNums)->toContain(30001)
        ->and($sumulaNums)->not->toContain(30002);
});

it('executeResult inclui fixture de tese no shape de hits com MySQL real', function () {
    $tribunalConfig = config('tes_constants.lista_tribunais.STJ');
    $svc = app(SearchDatabaseService::class);

    $result = $svc->executeResult('indenizacao moral', 'stj', $tribunalConfig);

    $teseNums = array_column($result->tese()->hits(), 'trib_rep_numero');
    expect($teseNums)->toContain(30001)
        ->and($teseNums)->not->toContain(30002);
});

it('executeResult exclui resultado pelo NOT operator com MySQL real', function () {
    $tribunalConfig = config('tes_constants.lista_tribunais.STJ');
    $svc = app(SearchDatabaseService::class);

    // "indenizacao não moral" → +indenizacao -moral → fixture 30001 excluída
    $result = $svc->executeResult('indenizacao não moral', 'stj', $tribunalConfig);

    $sumulaNums = array_column($result->sumula()->hits(), 'trib_sum_numero');
    expect($sumulaNums)->not->toContain(30001)
        ->and($sumulaNums)->toContain(30002);
});

it('executeResult toArray preserva o shape legado de array esperado pelos controllers', function () {
    $tribunalConfig = config('tes_constants.lista_tribunais.STJ');
    $svc = app(SearchDatabaseService::class);

    $array = $svc->executeResult('indenizacao moral', 'stj', $tribunalConfig)->toArray();

    expect($array)->toHaveKey('sumula')
        ->and($array)->toHaveKey('tese')
        ->and($array)->toHaveKey('total_count')
        ->and($array['sumula'])->toHaveKey('total')
        ->and($array['sumula'])->toHaveKey('hits')
        ->and($array['tese'])->toHaveKey('total')
        ->and($array['tese'])->toHaveKey('hits');
});
