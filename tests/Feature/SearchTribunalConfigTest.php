<?php

use App\Services\SearchTribunalConfig;

it('exposes tribunal config through a stable object API', function () {
    $config = SearchTribunalConfig::fromArray('STJ', config('tes_constants.lista_tribunais.STJ'));

    expect($config->tribunalUpper())->toBe('STJ')
        ->and($config->tribunalLower())->toBe('stj')
        ->and($config->usesDatabase())->toBeTrue()
        ->and($config->teseName())->toBe('tese')
        ->and($config->tables())->toHaveKey('sumulas')
        ->and($config->matchColumnsFor('sum'))->toBe('texto_raw,ramos')
        ->and($config->matchColumnsFor('rep'))->toBe('tese_texto,tema,ramos');
});

it('returns empty match columns for unsupported item types', function () {
    $config = SearchTribunalConfig::fromArray('TCU', config('tes_constants.lista_tribunais.TCU'));

    expect($config->usesDatabase())->toBeFalse()
        ->and($config->matchColumnsFor('unknown'))->toBe('');
});
