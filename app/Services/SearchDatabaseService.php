<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SearchDatabaseService
{
    public function __construct(
        protected SearchQueryParser $parser,
        protected SearchTribunalRegistry $tribunalRegistry,
        protected SearchCacheManager $cacheManager,
    ) {}

    public function search(string $keyword, string $tribunalLower, array|\App\Services\SearchTribunalConfig $tribunalConfig): array
    {
        $tribunalConfig = $this->normalizeTribunalConfig($tribunalLower, $tribunalConfig);

        return $this->cacheManager->remember($tribunalLower, $keyword, function () use ($keyword, $tribunalLower, $tribunalConfig): array {
            return $this->execute($keyword, $tribunalLower, $tribunalConfig);
        });
    }

    public function searchResult(string $keyword, string $tribunalLower, array|\App\Services\SearchTribunalConfig $tribunalConfig): SearchTribunalResult
    {
        $tribunalConfig = $this->normalizeTribunalConfig($tribunalLower, $tribunalConfig);

        return SearchTribunalResult::fromArray(
            $tribunalConfig->teseName(),
            $this->search($keyword, $tribunalLower, $tribunalConfig),
        );
    }

    public function execute(string $keyword, string $tribunalLower, array|\App\Services\SearchTribunalConfig $tribunalConfig): array
    {
        return $this->executeResult($keyword, $tribunalLower, $tribunalConfig)->toArray();
    }

    public function executeResult(string $keyword, string $tribunalLower, array|\App\Services\SearchTribunalConfig $tribunalConfig): SearchTribunalResult
    {
        $tribunalConfig = $this->normalizeTribunalConfig($tribunalLower, $tribunalConfig);
        $teseName = $tribunalConfig->teseName();
        $output = SearchTribunalResult::empty($teseName);
        $finalString = $this->buildBooleanSearchString($keyword);

        foreach ($tribunalConfig->tables() as $table => $configuredTables) {
            if (empty($configuredTables)) {
                continue;
            }

            [$key, $itemType] = $this->resolveSearchKeys($table, $teseName);
            if ($key === '' || $itemType === '') {
                continue;
            }

            foreach ($configuredTables as $configuredTable) {
                $tableName = $tribunalLower.'_'.$configuredTable;
                $toMatch = $tribunalConfig->matchColumnsFor($itemType);
                $query = "MATCH ({$toMatch}) AGAINST (? IN BOOLEAN MODE)";
                $results = DB::table($tableName)
                    ->whereRaw($query, [$finalString])
                    ->orderBy('numero', 'desc')
                    ->get();

                $results = json_decode(json_encode($results), true);

                if ($results) {
                    $adjustedResults = call_adjust_query_function($tribunalLower, $itemType, $results);
                    $output->addHits($key, $adjustedResults);
                }
            }
        }

        return $output;
    }

    /**
     * @return array<string, SearchTribunalResult>
     */
    public function searchAllDatabaseTribunals(string $keyword): array
    {
        $results = [];

        foreach ($this->tribunalRegistry->databaseEnabled() as $tribunalUpper => $config) {
            $results[strtolower($tribunalUpper)] = $this->searchResult($keyword, strtolower($tribunalUpper), $config);
        }

        return $results;
    }

    public function buildBooleanSearchString(string $keyword): string
    {
        return $this->parser->buildFinalSearchString(
            $this->parser->insertOperator($this->parser->keywordToArray($keyword))
        );
    }

    public function countBooleanModeMatches(string $keyword, string $tableName, string $toMatch): int
    {
        $query = "MATCH ({$toMatch}) AGAINST (? IN BOOLEAN MODE)";

        return DB::table($tableName)
            ->whereRaw($query, [$this->buildBooleanSearchString($keyword)])
            ->count();
    }

    private function resolveSearchKeys(string $table, string $teseName): array
    {
        if ($table === 'sumulas') {
            return ['sumula', 'sum'];
        }

        if ($table === 'teses') {
            return [$teseName, 'rep'];
        }

        return ['', ''];
    }

    private function normalizeTribunalConfig(string $tribunalLower, array|\App\Services\SearchTribunalConfig $tribunalConfig): \App\Services\SearchTribunalConfig
    {
        if ($tribunalConfig instanceof \App\Services\SearchTribunalConfig) {
            return $tribunalConfig;
        }

        return \App\Services\SearchTribunalConfig::fromArray(strtoupper($tribunalLower), $tribunalConfig);
    }
}
