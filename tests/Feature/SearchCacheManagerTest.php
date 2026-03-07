<?php

use App\Services\SearchCacheManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

it('generates deterministic cache keys using tribunal and keyword md5', function () {
    $manager = app(SearchCacheManager::class);

    expect($manager->cacheKey('stf', 'dano moral'))
        ->toBe('search_stf_'.md5('dano moral'));
});

it('caches search results on first call and returns cached on second call', function () {
    $manager = app(SearchCacheManager::class);
    $callCount = 0;

    $callback = function () use (&$callCount): array {
        $callCount++;

        return ['total_count' => 5];
    };

    $first = $manager->remember('stf', 'dano moral', $callback);
    $second = $manager->remember('stf', 'dano moral', $callback);

    expect($first)->toBe(['total_count' => 5])
        ->and($second)->toBe(['total_count' => 5])
        ->and($callCount)->toBe(1);
});

it('falls back to direct execution when cache throws an exception', function () {
    Cache::shouldReceive('getStore')->andThrow(new \RuntimeException('store broke'));

    Log::shouldReceive('warning')
        ->once()
        ->with(Mockery::on(fn (string $msg): bool => str_contains($msg, 'Cache de busca falhou')));

    $manager = app(SearchCacheManager::class);
    $result = $manager->remember('stf', 'dano moral', fn (): array => ['total_count' => 3]);

    expect($result)->toBe(['total_count' => 3]);
});

it('forgets a specific cached search entry', function () {
    $manager = app(SearchCacheManager::class);

    $manager->remember('stj', 'icms', fn (): array => ['total_count' => 10]);

    // Verify it was cached (second call should not invoke callback)
    $callCount = 0;
    $manager->remember('stj', 'icms', function () use (&$callCount): array {
        $callCount++;

        return ['total_count' => 99];
    });
    expect($callCount)->toBe(0);

    $manager->forget('stj', 'icms');

    // After forget, callback should be invoked again
    $callCount = 0;
    $manager->remember('stj', 'icms', function () use (&$callCount): array {
        $callCount++;

        return ['total_count' => 99];
    });
    expect($callCount)->toBe(1);
});

it('flushes all search cache entries', function () {
    $manager = app(SearchCacheManager::class);

    $manager->remember('stf', 'dano', fn (): array => ['total_count' => 1]);
    $manager->remember('stj', 'moral', fn (): array => ['total_count' => 2]);

    $manager->flush();

    // After flush, callbacks should be invoked again
    $stfCount = 0;
    $manager->remember('stf', 'dano', function () use (&$stfCount): array {
        $stfCount++;

        return ['total_count' => 1];
    });

    $stjCount = 0;
    $manager->remember('stj', 'moral', function () use (&$stjCount): array {
        $stjCount++;

        return ['total_count' => 2];
    });

    expect($stfCount)->toBe(1)
        ->and($stjCount)->toBe(1);
});

it('forgets tribunal-specific cache without affecting other tribunals', function () {
    $manager = app(SearchCacheManager::class);

    if (! $manager->supportsTags()) {
        $this->markTestSkipped('Tag support required');
    }

    $manager->remember('stf', 'dano moral', fn (): array => ['total_count' => 3]);
    $manager->remember('stj', 'dano moral', fn (): array => ['total_count' => 7]);

    $manager->forgetTribunal('stf');

    // STF should be cleared (callback invoked again)
    $stfCount = 0;
    $manager->remember('stf', 'dano moral', function () use (&$stfCount): array {
        $stfCount++;

        return ['total_count' => 99];
    });

    // STJ should still be cached (callback NOT invoked)
    $stjCount = 0;
    $manager->remember('stj', 'dano moral', function () use (&$stjCount): array {
        $stjCount++;

        return ['total_count' => 99];
    });

    expect($stfCount)->toBe(1)
        ->and($stjCount)->toBe(0);
});

it('keeps separate cache entries per tribunal for the same keyword', function () {
    $manager = app(SearchCacheManager::class);

    $stf = $manager->remember('stf', 'dano moral', fn (): array => ['total_count' => 3]);
    $stj = $manager->remember('stj', 'dano moral', fn (): array => ['total_count' => 7]);

    expect($stf)->toBe(['total_count' => 3])
        ->and($stj)->toBe(['total_count' => 7]);
});
