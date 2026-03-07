<?php

namespace App\Services;

use Closure;
use Exception;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SearchCacheManager
{
    private const TAG_ALL = 'search';

    private const TTL_SECONDS = 3600;

    public function cacheKey(string $tribunalLower, string $keyword): string
    {
        return 'search_'.$tribunalLower.'_'.md5($keyword);
    }

    public function remember(string $tribunalLower, string $keyword, Closure $callback): array
    {
        $key = $this->cacheKey($tribunalLower, $keyword);

        try {
            if ($this->supportsTags()) {
                return Cache::tags([self::TAG_ALL, $this->tagForTribunal($tribunalLower)])
                    ->remember($key, self::TTL_SECONDS, $callback);
            }

            return Cache::remember($key, self::TTL_SECONDS, $callback);
        } catch (Exception $e) {
            Log::warning('Cache de busca falhou, executando sem cache: '.$e->getMessage());

            return $callback();
        }
    }

    public function forget(string $tribunalLower, string $keyword): bool
    {
        $key = $this->cacheKey($tribunalLower, $keyword);

        if ($this->supportsTags()) {
            return Cache::tags([self::TAG_ALL, $this->tagForTribunal($tribunalLower)])->forget($key);
        }

        return Cache::forget($key);
    }

    public function forgetTribunal(string $tribunalLower): bool
    {
        if ($this->supportsTags()) {
            return Cache::tags([$this->tagForTribunal($tribunalLower)])->flush();
        }

        Log::info("SearchCacheManager: driver sem suporte a tags; forgetTribunal({$tribunalLower}) requer flush completo.");

        return false;
    }

    public function flush(): bool
    {
        if ($this->supportsTags()) {
            return Cache::tags([self::TAG_ALL])->flush();
        }

        return Cache::flush();
    }

    public function supportsTags(): bool
    {
        return Cache::getStore() instanceof TaggableStore;
    }

    private function tagForTribunal(string $tribunalLower): string
    {
        return 'search_'.$tribunalLower;
    }
}
