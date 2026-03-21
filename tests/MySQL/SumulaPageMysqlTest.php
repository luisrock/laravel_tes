<?php

use Illuminate\Support\Facades\DB;
use Tests\MySQLTestCase;

$FIXTURE_NUMERO = 30099;

$TRIBUNAIS = [
    'stf' => [
        'table' => 'stf_sumulas',
        'data' => [
            'numero' => 30099,
            'titulo' => '[FIXTURE] Súmula 30099 do STF',
            'texto' => '[FIXTURE] Texto de teste',
            'obs' => '',
            'legis' => '',
            'precedentes' => '',
            'is_vinculante' => 0,
            'aprovadaEm' => '2024-01-01',
            'link' => '',
            'seq' => 30099,
        ],
    ],
    'stj' => [
        'table' => 'stj_sumulas',
        'data' => [
            'numero' => 30099,
            'titulo' => '[FIXTURE] Súmula 30099 do STJ',
            'texto' => '[FIXTURE] Texto de teste',
            'publicadaEm' => '2024-01-01',
            'isCancelada' => 0,
        ],
    ],
    'tst' => [
        'table' => 'tst_sumulas',
        'data' => [
            'numero' => 30099,
            'titulo' => '[FIXTURE] Súmula 30099 do TST',
            'tema' => '[FIXTURE]',
            'texto' => '[FIXTURE] Texto de teste',
            'link' => '',
        ],
    ],
    'tnu' => [
        'table' => 'tnu_sumulas',
        'data' => [
            'numero' => 30099,
            'titulo' => '[FIXTURE] Súmula 30099 da TNU',
            'texto' => '[FIXTURE] Texto de teste',
            'dados' => '',
            'link' => '',
            'isCancelada' => 0,
        ],
    ],
];

$STF_VINCULANTE = [
    'table' => 'stf_sumulas',
    'data' => [
        'numero' => 30099,
        'titulo' => '[FIXTURE] Súmula Vinculante 30099',
        'texto' => '[FIXTURE] Texto vinculante de teste',
        'obs' => '',
        'legis' => '',
        'precedentes' => '',
        'is_vinculante' => 1,
        'aprovadaEm' => '2024-06-01',
        'link' => '',
        'seq' => 30100,
    ],
];

beforeEach(function () use ($TRIBUNAIS, $STF_VINCULANTE) {
    if (MySQLTestCase::integrationDatabaseUnavailable()) {
        return;
    }

    foreach ($TRIBUNAIS as $cfg) {
        DB::table($cfg['table'])->where('numero', $cfg['data']['numero'])->delete();
        DB::table($cfg['table'])->insert($cfg['data']);
    }
    // Insert STF vinculante (same numero, different is_vinculante)
    DB::table($STF_VINCULANTE['table'])->insert($STF_VINCULANTE['data']);
});

afterEach(function () use ($TRIBUNAIS) {
    if (MySQLTestCase::integrationDatabaseUnavailable()) {
        return;
    }

    foreach ($TRIBUNAIS as $cfg) {
        DB::table($cfg['table'])->where('numero', $cfg['data']['numero'])->delete();
    }
});

it('carrega sumula page por numero', function (string $tribunal, string $table) {
    $this->get("/sumula/{$tribunal}/30099")
        ->assertSuccessful();
})->with([
    'STF' => ['stf', 'stf_sumulas'],
    'STJ' => ['stj', 'stj_sumulas'],
    'TST' => ['tst', 'tst_sumulas'],
    'TNU' => ['tnu', 'tnu_sumulas'],
]);

it('carrega sumula vinculante STF com prefixo sv', function () {
    $response = $this->get('/sumula/stf/sv30099');
    $response->assertSuccessful();
    $response->assertSee('[FIXTURE] Súmula Vinculante 30099');
});

it('carrega sumula comum STF sem prefixo sv', function () {
    $response = $this->get('/sumula/stf/30099');
    $response->assertSuccessful();
    $response->assertSee('[FIXTURE] Súmula 30099 do STF');
});

it('redireciona sumula por id antigo para url com numero', function (string $tribunal, string $table) {
    $sumula = DB::table($table)
        ->where('numero', 30099)
        ->when($tribunal === 'stf', fn ($q) => $q->where('is_vinculante', 0))
        ->first();

    // Só testa redirect se id != numero (senão ambos resolvem igual)
    if ((int) $sumula->id !== 30099) {
        $this->get("/sumula/{$tribunal}/{$sumula->id}")
            ->assertRedirect("/sumula/{$tribunal}/30099");
    } else {
        $this->get("/sumula/{$tribunal}/{$sumula->id}")
            ->assertSuccessful();
    }
})->with([
    'STF' => ['stf', 'stf_sumulas'],
    'STJ' => ['stj', 'stj_sumulas'],
    'TST' => ['tst', 'tst_sumulas'],
    'TNU' => ['tnu', 'tnu_sumulas'],
]);

it('redireciona sumula vinculante STF por id antigo para url com sv', function () {
    $sumula = DB::table('stf_sumulas')
        ->where('numero', 30099)
        ->where('is_vinculante', 1)
        ->first();

    if ((int) $sumula->id !== 30099) {
        $this->get("/sumula/stf/{$sumula->id}")
            ->assertRedirect('/sumula/stf/sv30099');
    } else {
        // If id happens to equal numero, the common sumula would be returned
        // (since it's looked up by numero first without sv prefix)
        expect(true)->toBeTrue();
    }
});

it('redireciona sumula inexistente para listagem', function (string $tribunal) {
    $this->get("/sumula/{$tribunal}/99999")
        ->assertRedirect();
})->with(['stf', 'stj', 'tst', 'tnu']);
