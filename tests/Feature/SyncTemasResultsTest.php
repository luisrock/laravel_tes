<?php

use App\Services\SearchDatabaseService;
use Illuminate\Support\Facades\DB;

it('uses SearchDatabaseService to recalculate results in dry-run mode', function () {
    $temasBuilder = Mockery::mock();
    $temasBuilder->shouldReceive('get')
        ->once()
        ->with(['id', 'keyword', 'results'])
        ->andReturn(collect([
            (object) ['id' => 10, 'keyword' => 'dano moral', 'results' => 5],
        ]));

    DB::shouldReceive('table')
        ->once()
        ->with('pesquisas')
        ->andReturn($temasBuilder);

    $service = Mockery::mock(SearchDatabaseService::class);
    $expectedCalls = 0;
    foreach (config('tes_constants.lista_tribunais') as $tribunal => $config) {
        if ($config['db'] === false) {
            continue;
        }

        foreach ($config['tables'] as $table => $tab) {
            if (empty($tab)) {
                continue;
            }

            $it = $table === 'sumulas' ? 'sum' : 'rep';
            if (empty($config["to_match_{$it}"])) {
                continue;
            }

            $expectedCalls += count($tab);
        }
    }

    $service->shouldReceive('countBooleanModeMatches')
        ->times($expectedCalls)
        ->andReturnUsing(function (string $keyword, string $tableName, string $toMatch): int {
            return $tableName === 'stf_sumulas' ? 1 : 0;
        });

    app()->instance(SearchDatabaseService::class, $service);

    $this->artisan('temas:sync-results --dry-run')
        ->assertSuccessful();
});
