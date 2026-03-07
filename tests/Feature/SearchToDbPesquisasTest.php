<?php

use App\Jobs\SearchToDbPesquisas;
use App\Services\SearchDatabaseService;
use App\Services\SearchTribunalConfig;
use App\Services\SearchTribunalRegistry;
use App\Services\SearchTribunalResult;
use Illuminate\Support\Facades\DB;

it('stores the keyword when local search finds results', function () {
    $service = Mockery::mock(SearchDatabaseService::class);
    $listaTribunais = config('tes_constants.lista_tribunais');

    foreach ($listaTribunais as $tribunal => $tribunalConfig) {
        if ($tribunalConfig['db'] === false && $tribunal !== 'STF') {
            continue;
        }

        $service->shouldReceive('searchResult')
            ->once()
            ->with('dano moral', strtolower($tribunal), Mockery::on(function (mixed $config) use ($tribunal, $tribunalConfig): bool {
                return $config instanceof SearchTribunalConfig
                    && $config->tribunalUpper() === $tribunal
                    && $config->toArray() === $tribunalConfig;
            }))
            ->andReturnUsing(fn (): SearchTribunalResult => SearchTribunalResult::fromArray('tese', [
                'sumula' => ['total' => 0, 'hits' => []],
                'tese' => ['total' => $tribunal === 'STF' ? 2 : 0, 'hits' => []],
                'total_count' => $tribunal === 'STF' ? 2 : 0,
            ]));
    }

    $builder = Mockery::mock();
    $builder->shouldReceive('insertOrIgnore')
        ->once()
        ->with([
            'keyword' => 'dano moral',
            'results' => 2,
        ]);

    DB::shouldReceive('table')
        ->once()
        ->with('pesquisas')
        ->andReturn($builder);

    (new SearchToDbPesquisas('dano moral'))->handle($service, app(SearchTribunalRegistry::class));
});

it('ignores numeric keywords before querying the search service', function () {
    $service = Mockery::mock(SearchDatabaseService::class);
    $service->shouldNotReceive('searchResult');

    DB::shouldReceive('table')->never();

    (new SearchToDbPesquisas('123'))->handle($service, app(SearchTribunalRegistry::class));
});

it('ignores keywords containing sumula variant before querying the search service', function () {
    $service = Mockery::mock(SearchDatabaseService::class);
    $service->shouldNotReceive('searchResult');

    DB::shouldReceive('table')->never();

    (new SearchToDbPesquisas('Súmula 123'))->handle($service, app(SearchTribunalRegistry::class));
});

it('ignores keywords too short before querying the search service', function () {
    $service = Mockery::mock(SearchDatabaseService::class);
    $service->shouldNotReceive('searchResult');

    DB::shouldReceive('table')->never();

    (new SearchToDbPesquisas('ab'))->handle($service, app(SearchTribunalRegistry::class));
});
