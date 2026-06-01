<?php

namespace App\Services\Ai;

use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EligibleTemasQuery
{
    /**
     * @param  array<string, mixed>|null  $filters
     * @return LengthAwarePaginator<int, object>
     */
    public function paginate(
        ?array $filters,
        ?string $search,
        ?string $sortColumn,
        ?string $sortDirection,
        int $page,
        int $perPage,
    ): LengthAwarePaginator {
        $tribunal = $this->tribunalFromFilters($filters);
        $hasAi = $this->hasAiFromFilters($filters);
        $onlyTransito = $this->onlyTransitoFromFilters($filters);

        $query = $this->baseQuery($tribunal);
        $table = $tribunal === 'STF' ? 'stf_teses' : 'stj_teses';
        $temaColumn = $tribunal === 'STF' ? 'tema_texto' : 'tema';

        if ($onlyTransito) {
            $query->where("{$table}.situacao", 'like', '%Trânsito%');
        }

        if ($hasAi === true) {
            $query->having('ia_sections_count', '>', 0);
        } elseif ($hasAi === false) {
            $query->having('ia_sections_count', '=', 0);
        }

        if (filled($search)) {
            $query->where(function (Builder $q) use ($search, $table, $temaColumn): void {
                $q->where("{$table}.{$temaColumn}", 'like', "%{$search}%")
                    ->orWhere("{$table}.numero", 'like', "%{$search}%");
            });
        }

        $sortColumn = in_array($sortColumn, ['numero', 'acordaos_count'], true)
            ? $sortColumn
            : 'numero';
        $sortDirection = strtolower((string) $sortDirection) === 'asc' ? 'asc' : 'desc';

        if ($sortColumn === 'acordaos_count') {
            $query->orderBy('acordaos_count', $sortDirection)
                ->orderBy("{$table}.numero", $sortDirection);
        } else {
            $query->orderBy("{$table}.numero", $sortDirection);
        }

        $paginator = $query->paginate(
            perPage: max(1, $perPage),
            page: max(1, $page),
        );

        $paginator->getCollection()->transform(function (object $row) use ($tribunal): array {
            $teseId = (int) $row->tese_id;
            $iaSectionsCount = (int) $row->ia_sections_count;
            $jobStatus = $row->job_status !== null ? (string) $row->job_status : null;

            return [
                '__key' => "{$tribunal}:{$teseId}",
                'tese_id' => $teseId,
                'tribunal' => $tribunal,
                'numero' => (int) $row->numero,
                'descricao' => (string) $row->descricao,
                'acordaos_count' => (int) $row->acordaos_count,
                'job_status' => $jobStatus,
                'has_ai' => $iaSectionsCount > 0,
                'is_eligible' => $iaSectionsCount === 0
                    && ! in_array($jobStatus, ['queued', 'running'], true),
            ];
        });

        /** @var LengthAwarePaginator<int, array<string, mixed>> */
        return $paginator;
    }

    /**
     * @param  array<string, mixed>|null  $filters
     */
    private function tribunalFromFilters(?array $filters): string
    {
        $value = $this->filterValue($filters, 'tribunal') ?? 'STF';

        return strtoupper((string) $value) === 'STJ' ? 'STJ' : 'STF';
    }

    /**
     * @param  array<string, mixed>|null  $filters
     */
    private function hasAiFromFilters(?array $filters): ?bool
    {
        $value = $this->filterValue($filters, 'has_ai');

        if ($value === null || $value === '') {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /**
     * @param  array<string, mixed>|null  $filters
     */
    private function onlyTransitoFromFilters(?array $filters): bool
    {
        $value = $this->filterValue($filters, 'only_transito');

        if ($value === null || $value === '') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param  array<string, mixed>|null  $filters
     */
    private function filterValue(?array $filters, string $key): mixed
    {
        if ($filters === null) {
            return null;
        }

        $value = $filters[$key] ?? null;

        if (is_array($value)) {
            return $value['value'] ?? $value['values'] ?? reset($value);
        }

        return $value;
    }

    private function baseQuery(string $tribunal): Builder
    {
        $table = $tribunal === 'STF' ? 'stf_teses' : 'stj_teses';
        $temaColumn = $tribunal === 'STF' ? 'tema_texto' : 'tema';

        $selectColumns = [
            "{$table}.id as tese_id",
            "{$table}.numero",
            "{$table}.{$temaColumn} as descricao",
            DB::raw('COUNT(DISTINCT tese_acordaos.id) as acordaos_count'),
            DB::raw('COUNT(DISTINCT tese_analysis_sections.id) as ia_sections_count'),
            'tese_analysis_jobs.status as job_status',
        ];

        $groupByColumns = [
            "{$table}.id",
            "{$table}.numero",
            "{$table}.{$temaColumn}",
            "{$table}.situacao",
            'tese_analysis_jobs.status',
        ];

        return DB::table($table)
            ->select($selectColumns)
            ->leftJoin('tese_acordaos', function ($join) use ($table, $tribunal): void {
                $join->on('tese_acordaos.tese_id', '=', "{$table}.id")
                    ->where('tese_acordaos.tribunal', '=', $tribunal)
                    ->whereNull('tese_acordaos.deleted_at');
            })
            ->leftJoin('tese_analysis_sections', function ($join) use ($table, $tribunal): void {
                $join->on('tese_analysis_sections.tese_id', '=', "{$table}.id")
                    ->where('tese_analysis_sections.tribunal', '=', $tribunal);
            })
            ->leftJoin('tese_analysis_jobs', function ($join) use ($table, $tribunal): void {
                $join->on('tese_analysis_jobs.tese_id', '=', "{$table}.id")
                    ->where('tese_analysis_jobs.tribunal', '=', $tribunal)
                    ->where('tese_analysis_jobs.section_type', '=', 'all');
            })
            ->groupBy($groupByColumns)
            ->having('acordaos_count', '>', 0);
    }
}
