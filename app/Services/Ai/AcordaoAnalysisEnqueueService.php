<?php

namespace App\Services\Ai;

use App\Jobs\AnalisarAcordaoJob;
use App\Models\AiModel;
use App\Models\SiteSetting;
use App\Models\TeseAnalysisJob;
use App\Models\TeseAnalysisSection;

class AcordaoAnalysisEnqueueService
{
    public function __construct(private AiModelResolver $resolver) {}

    public function resolveConfiguredModel(): AiModel
    {
        $slug = SiteSetting::get(
            'acordao_analysis_model',
            config('ai.acordao_analysis.default_model')
        );

        return $this->resolver->resolveOpenRouterModel((string) $slug);
    }

    public function hasActiveJob(int $teseId, string $tribunal): bool
    {
        return TeseAnalysisJob::query()
            ->where('tese_id', $teseId)
            ->where('tribunal', strtoupper($tribunal))
            ->where('section_type', 'all')
            ->whereIn('status', ['queued', 'running'])
            ->exists();
    }

    public function hasAiSections(int $teseId, string $tribunal): bool
    {
        return TeseAnalysisSection::query()
            ->where('tese_id', $teseId)
            ->where('tribunal', strtoupper($tribunal))
            ->exists();
    }

    public function isEligible(int $teseId, string $tribunal): bool
    {
        return ! $this->hasAiSections($teseId, $tribunal)
            && ! $this->hasActiveJob($teseId, $tribunal);
    }

    public function enqueue(
        int $teseId,
        string $tribunal,
        bool $force = false,
        ?string $modelSlug = null,
    ): ?TeseAnalysisJob {
        $tribunal = strtoupper($tribunal);

        if (! $force) {
            if ($this->hasActiveJob($teseId, $tribunal)) {
                return null;
            }

            if ($this->hasAiSections($teseId, $tribunal)) {
                return null;
            }
        } else {
            TeseAnalysisJob::query()
                ->where('tese_id', $teseId)
                ->where('tribunal', $tribunal)
                ->where('section_type', 'all')
                ->where('status', 'running')
                ->update([
                    'status' => 'error',
                    'last_error' => 'Interrompido: novo enfileiramento forçado pelo admin.',
                    'completed_at' => now(),
                    'locked_by' => null,
                ]);
        }

        $aiModel = filled($modelSlug)
            ? $this->resolver->resolveOpenRouterModel($modelSlug)
            : $this->resolveConfiguredModel();

        $job = TeseAnalysisJob::updateOrCreate(
            [
                'tese_id' => $teseId,
                'tribunal' => $tribunal,
                'section_type' => 'all',
            ],
            [
                'status' => 'queued',
                'ai_model_id' => $aiModel->id,
                'attempts' => 0,
                'last_error' => null,
                'locked_by' => null,
                'started_at' => null,
                'completed_at' => null,
            ]
        );

        // Com QUEUE_CONNECTION=sync (dev), o job não pode rodar nesta requisição Livewire
        // (análise multimodal leva minutos e causa timeout no botão de enfileirar).
        AnalisarAcordaoJob::dispatch($job->id)->afterResponse();

        return $job;
    }

    public function dequeue(int $teseId, string $tribunal): bool
    {
        return TeseAnalysisJob::query()
            ->where('tese_id', $teseId)
            ->where('tribunal', strtoupper($tribunal))
            ->where('section_type', 'all')
            ->where('status', 'queued')
            ->delete() > 0;
    }

    /**
     * Remove o job do tema (qualquer status) para permitir novo enfileiramento.
     */
    public function removeJob(int $teseId, string $tribunal): bool
    {
        return TeseAnalysisJob::query()
            ->where('tese_id', $teseId)
            ->where('tribunal', strtoupper($tribunal))
            ->where('section_type', 'all')
            ->delete() > 0;
    }

    public function hasJob(int $teseId, string $tribunal): bool
    {
        return TeseAnalysisJob::query()
            ->where('tese_id', $teseId)
            ->where('tribunal', strtoupper($tribunal))
            ->where('section_type', 'all')
            ->exists();
    }

    /**
     * @param  array<int, array{tese_id: int, tribunal: string}>  $records
     */
    public function enqueueEligibleBatch(array $records, ?string $modelSlug = null): int
    {
        $count = 0;

        foreach ($records as $record) {
            if ($this->enqueue($record['tese_id'], $record['tribunal'], modelSlug: $modelSlug) !== null) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param  array<int, array{tese_id: int, tribunal: string}>  $records
     */
    public function dequeueBatch(array $records): int
    {
        $count = 0;

        foreach ($records as $record) {
            if ($this->dequeue($record['tese_id'], $record['tribunal'])) {
                $count++;
            }
        }

        return $count;
    }
}
