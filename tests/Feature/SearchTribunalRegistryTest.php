<?php

use App\Services\SearchTribunalConfig;
use App\Services\SearchTribunalRegistry;

it('returns raw and normalized tribunal configurations', function () {
    $registry = app(SearchTribunalRegistry::class);

    expect($registry->allRaw())->toHaveKey('STJ')
        ->and($registry->keys())->toContain('STJ')
        ->and($registry->all()['STJ'])->toBeInstanceOf(SearchTribunalConfig::class)
        ->and($registry->get('stj')->tribunalUpper())->toBe('STJ')
        ->and($registry->get('stj')->usesDatabase())->toBeTrue();
});

it('filters database enabled tribunals', function () {
    $registry = app(SearchTribunalRegistry::class);
    $databaseEnabled = $registry->databaseEnabled();

    expect($databaseEnabled)->toHaveKey('STF')
        ->and($databaseEnabled)->toHaveKey('STJ')
        ->and($databaseEnabled)->not->toHaveKey('TCU');
});
