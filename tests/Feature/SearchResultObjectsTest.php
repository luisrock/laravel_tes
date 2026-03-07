<?php

use App\Services\SearchResultSection;
use App\Services\SearchTribunalResult;

it('preserves the tribunal result array shape through the lightweight dto', function () {
    $result = SearchTribunalResult::empty('tese');

    $result->addHits('sumula', [
        ['trib_sum_numero' => '123'],
    ]);
    $result->addHits('tese', [
        ['trib_rep_numero' => '456'],
        ['trib_rep_numero' => '789'],
    ]);

    expect($result->sumula())->toBeInstanceOf(SearchResultSection::class)
        ->and($result->sumula()->total())->toBe(1)
        ->and($result->tese()->total())->toBe(2)
        ->and($result->totalCount())->toBe(3)
        ->and($result->toArray())->toBe([
            'sumula' => [
                'total' => 1,
                'hits' => [
                    ['trib_sum_numero' => '123'],
                ],
            ],
            'tese' => [
                'total' => 2,
                'hits' => [
                    ['trib_rep_numero' => '456'],
                    ['trib_rep_numero' => '789'],
                ],
            ],
            'total_count' => 3,
        ]);
});

it('rebuilds tribunal results from arrays without changing the public contract', function () {
    $result = SearchTribunalResult::fromArray('repercussao', [
        'sumula' => ['total' => 1, 'hits' => [['id' => 1]]],
        'repercussao' => ['total' => 2, 'hits' => [['id' => 2], ['id' => 3]]],
        'total_count' => 3,
    ]);

    expect($result->sumula()->total())->toBe(1)
        ->and($result->tese()->total())->toBe(2)
        ->and($result->totalCount())->toBe(3);
});

it('builds the public API response shape from tribunal results', function () {
    $result = SearchTribunalResult::fromArray('tese', [
        'sumula' => ['total' => 1, 'hits' => [['id' => 1]]],
        'tese' => ['total' => 2, 'hits' => [['id' => 2], ['id' => 3]]],
        'total_count' => 3,
    ]);

    expect($result->toPublicApiArray())->toBe([
        'total_sum' => 1,
        'total_rep' => 2,
        'hits_sum' => [['id' => 1]],
        'hits_rep' => [['id' => 2], ['id' => 3]],
    ]);
});

it('builds the unified summary shape from tribunal results', function () {
    $result = SearchTribunalResult::fromArray('repercussao', [
        'sumula' => ['total' => 1, 'hits' => []],
        'repercussao' => ['total' => 4, 'hits' => []],
        'total_count' => 5,
    ]);

    expect($result->toUnifiedSummaryArray())->toBe([
        'sumulas' => 1,
        'teses' => 4,
        'total' => 5,
    ]);
});
