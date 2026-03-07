<?php

namespace App\Jobs;

use App\Services\SearchDatabaseService;
use App\Services\SearchTribunalRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SearchToDbPesquisas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $keyword;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($keyword)
    {
        $this->keyword = $keyword;
    }

    /**
     * Execute the job.
     */
    public function handle(SearchDatabaseService $searchDatabaseService, SearchTribunalRegistry $searchTribunalRegistry): void
    {
        $tema = $this->keyword;
        $normalizedKeyword = mb_strtolower(trim($tema), 'UTF-8');

        if (
            is_numeric(trim($tema))
            || str_contains($normalizedKeyword, 'súmula')
            || str_contains($normalizedKeyword, 'sumula')
            || mb_strlen(trim($tema), 'UTF-8') < 3
        ) {
            return;
        }

        $total_count = 0;

        foreach ($searchTribunalRegistry->all() as $tribunal => $tribunalConfig) {
            if (! $tribunalConfig->usesDatabase()) {
                continue;
            }

            $tribunal_lower = strtolower($tribunal);
            $total_count += $searchDatabaseService->searchResult($tema, $tribunal_lower, $tribunalConfig)->totalCount();
        }

        if ($total_count > 0) {
            DB::table('pesquisas')->insertOrIgnore([
                'keyword' => $tema,
                'results' => $total_count,
            ]);
        }
    }
}
