<?php

use App\Services\SearchCacheManager;
use Illuminate\Support\Facades\Cache;

it('clears all search cache when run without options', function () {
    $manager = app(SearchCacheManager::class);

    $manager->remember('stf', 'dano moral', fn (): array => ['total_count' => 1]);
    $manager->remember('stj', 'icms', fn (): array => ['total_count' => 2]);

    $this->artisan('cache:clear-searches')
        ->assertSuccessful();

    expect(Cache::has($manager->cacheKey('stf', 'dano moral')))->toBeFalse()
        ->and(Cache::has($manager->cacheKey('stj', 'icms')))->toBeFalse();
});

it('fails with an invalid tribunal name', function () {
    $this->artisan('cache:clear-searches --tribunal=INVALIDO')
        ->assertFailed()
        ->expectsOutputToContain("Tribunal 'INVALIDO' não encontrado");
});

it('falls back to full flush for file/array driver with --tribunal', function () {
    $manager = app(SearchCacheManager::class);

    $manager->remember('stf', 'dano moral', fn (): array => ['total_count' => 1]);
    $manager->remember('stj', 'icms', fn (): array => ['total_count' => 2]);

    $this->artisan('cache:clear-searches --tribunal=STF')
        ->assertSuccessful();

    // Array driver has no tag support, so it falls back to full flush
    expect(Cache::has($manager->cacheKey('stf', 'dano moral')))->toBeFalse()
        ->and(Cache::has($manager->cacheKey('stj', 'icms')))->toBeFalse();
});
