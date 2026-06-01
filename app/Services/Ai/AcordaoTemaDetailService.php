<?php

namespace App\Services\Ai;

use App\Models\TeseAcordao;
use App\Models\TeseAnalysisJob;
use App\Models\TeseAnalysisSection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Carrega dados do detalhe de um tema STF/STJ para a tela Filament (paridade Flask /tema).
 */
class AcordaoTemaDetailService
{
    /** @var array<string, string> */
    public const SECTION_LABELS = [
        'teaser' => 'Teaser',
        'caso_fatico' => 'Caso fático',
        'contornos_juridicos' => 'Contornos jurídicos',
        'modulacao' => 'Modulação',
        'tese_explicada' => 'Tese explicada',
    ];

    /**
     * Carrega o tema pelo número público (URL canônica), não pelo id interno.
     *
     * @return array<string, mixed>|null
     */
    public function loadByNumero(string $tribunal, int $numero): ?array
    {
        $tribunal = strtoupper($tribunal);

        if (! in_array($tribunal, ['STF', 'STJ'], true)) {
            return null;
        }

        $teseId = $this->resolveTeseIdByNumero($tribunal, $numero);

        if ($teseId === null) {
            return null;
        }

        return $this->load($tribunal, $teseId);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function load(string $tribunal, int $teseId): ?array
    {
        $tribunal = strtoupper($tribunal);

        if (! in_array($tribunal, ['STF', 'STJ'], true)) {
            return null;
        }

        $tese = $this->loadTese($tribunal, $teseId);

        if ($tese === null) {
            return null;
        }

        $acordaos = TeseAcordao::forTese($teseId, $tribunal)
            ->orderByDesc('version')
            ->orderByDesc('created_at')
            ->get();

        $sections = $this->loadLatestSections($tribunal, $teseId);

        $jobs = TeseAnalysisJob::query()
            ->with('aiModel')
            ->where('tese_id', $teseId)
            ->where('tribunal', $tribunal)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $enqueue = app(AcordaoAnalysisEnqueueService::class);

        return [
            'tese_id' => $teseId,
            'tribunal' => $tribunal,
            'numero' => (int) $tese->numero,
            'descricao' => (string) ($tese->descricao ?? ''),
            'situacao' => $tese->situacao !== null ? (string) $tese->situacao : null,
            'tese_texto' => $tese->tese_texto !== null ? (string) $tese->tese_texto : null,
            'public_url' => $this->publicTeseUrl($tribunal, (int) $tese->numero),
            'acordaos' => $acordaos,
            'sections' => $sections,
            'jobs' => $jobs,
            'is_eligible' => $enqueue->isEligible($teseId, $tribunal),
            'active_job' => $jobs->first(fn (TeseAnalysisJob $job): bool => in_array($job->status, ['queued', 'running'], true)
                && $job->section_type === 'all'),
        ];
    }

    private function resolveTeseIdByNumero(string $tribunal, int $numero): ?int
    {
        $table = $tribunal === 'STF' ? 'stf_teses' : 'stj_teses';

        $id = DB::table($table)->where('numero', $numero)->value('id');

        return $id !== null ? (int) $id : null;
    }

    /**
     * @return object{id: int, numero: int, descricao: ?string, situacao: ?string, tese_texto: ?string}|null
     */
    private function loadTese(string $tribunal, int $teseId): ?object
    {
        $table = $tribunal === 'STF' ? 'stf_teses' : 'stj_teses';
        $temaColumn = $tribunal === 'STF' ? 'tema_texto' : 'tema';

        $row = DB::table($table)
            ->select([
                "{$table}.id",
                "{$table}.numero",
                "{$table}.{$temaColumn} as descricao",
                "{$table}.situacao",
                "{$table}.tese_texto",
            ])
            ->where("{$table}.id", $teseId)
            ->first();

        return $row;
    }

    /**
     * @return Collection<int, TeseAnalysisSection>
     */
    private function loadLatestSections(string $tribunal, int $teseId): Collection
    {
        return TeseAnalysisSection::query()
            ->with('aiModel')
            ->where('tese_id', $teseId)
            ->where('tribunal', $tribunal)
            ->orderByDesc('generated_at')
            ->get()
            ->unique('section_type')
            ->values();
    }

    private function publicTeseUrl(string $tribunal, int $numero): string
    {
        $route = $tribunal === 'STF' ? 'stftesepage' : 'stjtesepage';

        return route($route, ['tese' => $numero]);
    }
}
