<?php

namespace App\Console\Commands;

use App\Services\SearchDatabaseService;
use App\Services\SearchTribunalRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncTemasResults extends Command
{
    protected $signature = 'temas:sync-results {--dry-run : Apenas listar sem alterar}';

    protected $description = 'Recalcula a coluna results de todos os temas via FULLTEXT real e deleta temas com 0 resultados';

    public function handle(SearchDatabaseService $searchDatabaseService, SearchTribunalRegistry $searchTribunalRegistry): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 Modo dry-run: nenhuma alteração será feita.');
        }

        $temas = DB::table('pesquisas')->get(['id', 'keyword', 'results']);
        $total = $temas->count();

        $this->info("Processando {$total} temas...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $lista_tribunais = $searchTribunalRegistry->all();
        $updated = 0;
        $deleted = 0;
        $deletedList = [];

        foreach ($temas as $tema) {
            $realResults = $this->countRealResults($tema->keyword, $lista_tribunais, $searchDatabaseService);

            if ($realResults === 0) {
                $deleted++;
                $deletedList[] = "ID={$tema->id} results_old={$tema->results} keyword=[{$tema->keyword}]";

                if (! $dryRun) {
                    DB::table('pesquisas')->where('id', $tema->id)->delete();
                }
            } elseif ($realResults !== (int) $tema->results) {
                $updated++;

                if (! $dryRun) {
                    DB::table('pesquisas')->where('id', $tema->id)->update(['results' => $realResults]);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Resumo
        $this->info('📊 Resumo:');
        $this->info("   Total processado: {$total}");
        $this->info("   Atualizados (results corrigido): {$updated}");
        $this->info("   Deletados (0 resultados): {$deleted}");

        if ($deleted > 0 && $this->getOutput()->isVerbose()) {
            $this->newLine();
            $this->warn('Temas deletados:');
            foreach ($deletedList as $item) {
                $this->line("   {$item}");
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('⚠️  Nenhuma alteração foi feita (dry-run). Rode sem --dry-run para aplicar.');
        }

        return Command::SUCCESS;
    }

    /**
     * Conta os resultados reais via FULLTEXT para uma keyword em todos os tribunais.
     */
    private function countRealResults(string $keyword, array $lista_tribunais, SearchDatabaseService $searchDatabaseService): int
    {
        $total = 0;

        foreach ($lista_tribunais as $tribunal => $config) {
            if (! $config->usesDatabase()) {
                continue;
            }

            $trib_lower = strtolower($tribunal);
            $tables = $config->tables();

            foreach ($tables as $table => $tab) {
                if (empty($tab)) {
                    continue;
                }

                $it = ($table === 'sumulas') ? 'sum' : 'rep';
                $to_match = $config->matchColumnsFor($it);

                if (empty($to_match)) {
                    continue;
                }

                foreach ($tab as $t) {
                    $table_name = $trib_lower.'_'.$t;

                    try {
                        $count = $searchDatabaseService->countBooleanModeMatches($keyword, $table_name, $to_match);
                        $total += $count;
                    } catch (\Exception $e) {
                        // Tabela pode não existir ou coluna inválida, ignorar
                    }
                }
            }
        }

        return $total;
    }
}
