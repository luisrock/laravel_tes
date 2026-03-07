<?php

namespace App\Console\Commands;

use App\Services\SearchCacheManager;
use App\Services\SearchTribunalRegistry;
use Illuminate\Console\Command;

class ClearSearchCache extends Command
{
    protected $signature = 'cache:clear-searches
                            {--tribunal= : Limpar cache apenas de um tribunal específico (ex: STF, STJ)}';

    protected $description = 'Limpa o cache de buscas de jurisprudência';

    public function handle(SearchCacheManager $cacheManager, SearchTribunalRegistry $registry): int
    {
        $tribunal = $this->option('tribunal');

        if ($tribunal !== null) {
            return $this->clearTribunal($cacheManager, $registry, $tribunal);
        }

        return $this->clearAll($cacheManager);
    }

    private function clearTribunal(SearchCacheManager $cacheManager, SearchTribunalRegistry $registry, string $tribunal): int
    {
        $tribunalLower = strtolower($tribunal);

        if (! in_array(strtoupper($tribunal), $registry->keys(), true)) {
            $this->error("Tribunal '{$tribunal}' não encontrado. Válidos: ".implode(', ', $registry->keys()));

            return 1;
        }

        if ($cacheManager->forgetTribunal($tribunalLower)) {
            $this->info('Cache de buscas do '.strtoupper($tribunal).' limpo com sucesso.');

            return 0;
        }

        $this->warn('Driver de cache sem suporte a tags. Executando flush completo como fallback.');

        return $this->clearAll($cacheManager);
    }

    private function clearAll(SearchCacheManager $cacheManager): int
    {
        $cacheManager->flush();
        $this->info('Cache de buscas limpo com sucesso.');

        return 0;
    }
}
