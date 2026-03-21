<?php

use Illuminate\Support\Facades\DB;
use Tests\MySQLTestCase;

$FIXTURE_NUMERO = 30099;

$TRIBUNAIS = [
    'stf' => [
        'table' => 'stf_teses',
        'data' => [
            'numero' => 30099,
            'tema_texto' => '[FIXTURE] Tema de teste',
            'tese_texto' => '[FIXTURE] Tese de teste',
            'situacao' => 'Ativo',
            'relator' => '[FIXTURE]',
        ],
    ],
    'stj' => [
        'table' => 'stj_teses',
        'data' => [
            'numero' => 30099,
            'orgao' => '[FIXTURE]',
            'tema' => '[FIXTURE] Tema de teste',
            'tese_texto' => '[FIXTURE] Tese de teste',
            'ramos' => 'Direito Civil',
            'atualizadaEm' => '2024-01-01',
            'situacao' => 'Ativo',
        ],
    ],
    'tst' => [
        'table' => 'tst_teses',
        'data' => [
            'numero' => 30099,
            'titulo' => '[FIXTURE]',
            'tema' => '[FIXTURE] Tema de teste',
            'texto' => '[FIXTURE] Tese de teste',
            'tipo' => 'PN',
            'link' => '',
        ],
    ],
    'tnu' => [
        'table' => 'tnu_teses',
        'data' => [
            'numero' => 30099,
            'titulo' => '[FIXTURE]',
            'ramo' => 'Previdenciário',
            'tema' => '[FIXTURE] Tema de teste',
            'tese' => '[FIXTURE] Tese de teste',
            'relator' => '[FIXTURE]',
            'processo' => '0000000-00.0000.0.00.0000',
            'situacao' => 'Ativo',
            'link' => '',
        ],
    ],
];

beforeEach(function () use ($TRIBUNAIS) {
    if (MySQLTestCase::integrationDatabaseUnavailable()) {
        return;
    }

    foreach ($TRIBUNAIS as $cfg) {
        DB::table($cfg['table'])->where('numero', $cfg['data']['numero'])->delete();
        DB::table($cfg['table'])->insert($cfg['data']);
    }
});

afterEach(function () use ($TRIBUNAIS) {
    if (MySQLTestCase::integrationDatabaseUnavailable()) {
        return;
    }

    foreach ($TRIBUNAIS as $cfg) {
        DB::table($cfg['table'])->where('numero', $cfg['data']['numero'])->delete();
    }
});

it('carrega tese page por numero', function (string $tribunal, string $table) {
    config(['subscription.enabled' => false]);

    $this->get("/tese/{$tribunal}/30099")
        ->assertSuccessful();
})->with([
    'STF' => ['stf', 'stf_teses'],
    'STJ' => ['stj', 'stj_teses'],
    'TST' => ['tst', 'tst_teses'],
    'TNU' => ['tnu', 'tnu_teses'],
]);

it('redireciona tese por id antigo para url com numero', function (string $tribunal, string $table) {
    config(['subscription.enabled' => false]);

    $tese = DB::table($table)->where('numero', 30099)->first();

    // Só testa redirect se id != numero (senão ambos resolvem igual)
    if ((int) $tese->id !== 30099) {
        $this->get("/tese/{$tribunal}/{$tese->id}")
            ->assertRedirect("/tese/{$tribunal}/30099");
    } else {
        $this->get("/tese/{$tribunal}/{$tese->id}")
            ->assertSuccessful();
    }
})->with([
    'STF' => ['stf', 'stf_teses'],
    'STJ' => ['stj', 'stj_teses'],
    'TST' => ['tst', 'tst_teses'],
    'TNU' => ['tnu', 'tnu_teses'],
]);

it('redireciona tese inexistente para listagem', function (string $tribunal) {
    $this->get("/tese/{$tribunal}/99999")
        ->assertRedirect();
})->with(['stf', 'stj', 'tst', 'tnu']);
