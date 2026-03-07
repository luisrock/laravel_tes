<?php

use App\Services\SearchCacheManager;
use App\Services\SearchDatabaseService;
use App\Services\SearchTribunalRegistry;
use App\Services\SearchTribunalResult;
use Illuminate\Support\Facades\DB;

function mockSearchBuilder(string $tableName, string $expectedQuery, array $expectedBindings, array $results): void
{
    $builder = Mockery::mock();
    $builder->shouldReceive('whereRaw')
        ->once()
        ->with($expectedQuery, $expectedBindings)
        ->andReturnSelf();
    $builder->shouldReceive('orderBy')
        ->once()
        ->with('numero', 'desc')
        ->andReturnSelf();
    $builder->shouldReceive('get')
        ->once()
        ->andReturn(collect($results));

    DB::shouldReceive('table')
        ->once()
        ->with($tableName)
        ->andReturn($builder);
}

it('executes db search through cache preserving output shape', function () {
    $keyword = 'dano moral';
    $tribunalLower = 'stj';
    $tribunalConfig = config('tes_constants.lista_tribunais.STJ');
    $expectedSearchString = '+dano +moral';

    $cacheManager = Mockery::mock(SearchCacheManager::class);
    $cacheManager->shouldReceive('remember')
        ->once()
        ->with('stj', 'dano moral', Mockery::type(Closure::class))
        ->andReturnUsing(fn (string $t, string $k, Closure $callback): array => $callback());
    app()->instance(SearchCacheManager::class, $cacheManager);

    mockSearchBuilder(
        'stj_sumulas',
        'MATCH (texto_raw,ramos) AGAINST (? IN BOOLEAN MODE)',
        [$expectedSearchString],
        [[
            'titulo' => 'Sumula dano moral',
            'numero' => '123',
            'texto_raw' => 'Texto raw da sumula',
            'texto' => 'Texto da sumula',
            'julgadaEm' => '2024-01-15',
            'isCancelada' => null,
            'ramos' => 'Direito Civil',
            'dados' => 'dados',
            'id' => 1,
        ]]
    );

    mockSearchBuilder(
        'stj_teses',
        'MATCH (tese_texto,tema,ramos) AGAINST (? IN BOOLEAN MODE)',
        [$expectedSearchString],
        [[
            'numero' => '456',
            'orgao' => 'Primeira Secao',
            'tema' => 'Tema dano moral',
            'tese_texto' => 'Tese firmada',
            'ramos' => 'Direito Civil',
            'situacao' => 'Ativo',
            'atualizadaEm' => '2024-02-20',
            'id' => 2,
        ]]
    );

    $output = app(SearchDatabaseService::class)->search($keyword, $tribunalLower, $tribunalConfig);

    expect($output['sumula']['total'])->toBe(1)
        ->and($output['tese']['total'])->toBe(1)
        ->and($output['total_count'])->toBe(2)
        ->and($output['sumula']['hits'][0]['trib_sum_numero'])->toBe('123')
        ->and($output['tese']['hits'][0]['trib_rep_numero'])->toBe('456');
});

it('falls back to direct execution when cache manager executes callback', function () {
    $keyword = 'dano moral';
    $tribunalLower = 'stj';
    $tribunalConfig = config('tes_constants.lista_tribunais.STJ');
    $expectedSearchString = '+dano +moral';

    $cacheManager = Mockery::mock(SearchCacheManager::class);
    $cacheManager->shouldReceive('remember')
        ->once()
        ->andReturnUsing(fn (string $t, string $k, Closure $callback): array => $callback());
    app()->instance(SearchCacheManager::class, $cacheManager);

    mockSearchBuilder(
        'stj_sumulas',
        'MATCH (texto_raw,ramos) AGAINST (? IN BOOLEAN MODE)',
        [$expectedSearchString],
        []
    );

    mockSearchBuilder(
        'stj_teses',
        'MATCH (tese_texto,tema,ramos) AGAINST (? IN BOOLEAN MODE)',
        [$expectedSearchString],
        []
    );

    $output = app(SearchDatabaseService::class)->search($keyword, $tribunalLower, $tribunalConfig);

    expect($output['sumula']['total'])->toBe(0)
        ->and($output['tese']['total'])->toBe(0)
        ->and($output['total_count'])->toBe(0);
});

it('returns tribunal results through a lightweight dto without changing shape', function () {
    $keyword = 'dano moral';
    $tribunalLower = 'stj';
    $tribunalConfig = config('tes_constants.lista_tribunais.STJ');
    $expectedSearchString = '+dano +moral';

    $cacheManager = Mockery::mock(SearchCacheManager::class);
    $cacheManager->shouldReceive('remember')
        ->once()
        ->with('stj', 'dano moral', Mockery::type(Closure::class))
        ->andReturnUsing(fn (string $t, string $k, Closure $callback): array => $callback());
    app()->instance(SearchCacheManager::class, $cacheManager);

    mockSearchBuilder(
        'stj_sumulas',
        'MATCH (texto_raw,ramos) AGAINST (? IN BOOLEAN MODE)',
        [$expectedSearchString],
        [[
            'titulo' => 'Sumula dano moral',
            'numero' => '123',
            'texto_raw' => 'Texto raw da sumula',
            'texto' => 'Texto da sumula',
            'julgadaEm' => '2024-01-15',
            'isCancelada' => null,
            'ramos' => 'Direito Civil',
            'dados' => 'dados',
            'id' => 1,
        ]]
    );

    mockSearchBuilder(
        'stj_teses',
        'MATCH (tese_texto,tema,ramos) AGAINST (? IN BOOLEAN MODE)',
        [$expectedSearchString],
        [[
            'numero' => '456',
            'orgao' => 'Primeira Secao',
            'tema' => 'Tema dano moral',
            'tese_texto' => 'Tese firmada',
            'ramos' => 'Direito Civil',
            'situacao' => 'Ativo',
            'atualizadaEm' => '2024-02-20',
            'id' => 2,
        ]]
    );

    $output = app(SearchDatabaseService::class)->searchResult($keyword, $tribunalLower, $tribunalConfig);

    expect($output)->toBeInstanceOf(SearchTribunalResult::class)
        ->and($output->sumula()->total())->toBe(1)
        ->and($output->tese()->total())->toBe(1)
        ->and($output->totalCount())->toBe(2)
        ->and($output->toArray()['sumula']['hits'][0]['trib_sum_numero'])->toBe('123')
        ->and($output->toArray()['tese']['hits'][0]['trib_rep_numero'])->toBe('456');
});

it('counts matches in boolean mode using the parser output', function () {
    $builder = Mockery::mock();
    $builder->shouldReceive('whereRaw')
        ->once()
        ->with('MATCH (texto_raw,ramos) AGAINST (? IN BOOLEAN MODE)', ['+dano +moral'])
        ->andReturnSelf();
    $builder->shouldReceive('count')
        ->once()
        ->andReturn(7);

    DB::shouldReceive('table')
        ->once()
        ->with('stj_sumulas')
        ->andReturn($builder);

    $count = app(SearchDatabaseService::class)->countBooleanModeMatches('dano moral', 'stj_sumulas', 'texto_raw,ramos');

    expect($count)->toBe(7);
});

it('searches all database-enabled tribunals and excludes non-db tribunals', function () {
    $registry = app(SearchTribunalRegistry::class);
    $databaseEnabled = $registry->databaseEnabled();
    $emptyResult = SearchTribunalResult::fromArray('tese', [
        'sumula' => ['total' => 0, 'hits' => []],
        'tese' => ['total' => 0, 'hits' => []],
        'total_count' => 0,
    ]);

    $service = Mockery::mock(SearchDatabaseService::class, [app(\App\Services\SearchQueryParser::class), $registry, app(SearchCacheManager::class)])
        ->makePartial();

    foreach ($databaseEnabled as $tribunalUpper => $config) {
        $service->shouldReceive('searchResult')
            ->once()
            ->with('dano moral', strtolower($tribunalUpper), Mockery::on(fn ($c) => $c->tribunalUpper() === $tribunalUpper))
            ->andReturn($emptyResult);
    }

    $results = $service->searchAllDatabaseTribunals('dano moral');

    expect(array_keys($results))->toBe(array_map('strtolower', array_keys($databaseEnabled)))
        ->and($results)->not->toHaveKey('tcu');

    foreach ($results as $tribunalLower => $result) {
        expect($result)->toBeInstanceOf(SearchTribunalResult::class);
    }
});
