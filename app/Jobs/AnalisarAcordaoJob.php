<?php

namespace App\Jobs;

use App\Exceptions\AcordaoAnalysisPermanentException;
use App\Models\TeseAnalysisJob;
use App\Services\Ai\AcordaoAnalysisService;
use App\Support\AcordaoJobFailure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class AnalisarAcordaoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    /**
     * Análise multimodal com PDFs pode levar vários minutos (OpenRouter timeout ~120s + margem).
     */
    public int $timeout = 600;

    public function __construct(
        public int $teseAnalysisJobId,
    ) {}

    public function handle(AcordaoAnalysisService $service): void
    {
        set_time_limit($this->analysisTimeLimit());

        $record = TeseAnalysisJob::query()->find($this->teseAnalysisJobId);

        if ($record === null || $record->status !== 'queued') {
            return;
        }

        $record->update([
            'status' => 'running',
            'locked_by' => $this->queueWorkerId(),
            'started_at' => now(),
            'attempts' => $record->attempts + 1,
            'last_error' => null,
        ]);

        try {
            $aiModel = $record->aiModel;

            if ($aiModel === null) {
                throw new AcordaoAnalysisPermanentException('Modelo de IA não encontrado');
            }

            $result = $service->analyze(
                $record->tese_id,
                $record->tribunal,
                $aiModel,
                $record->section_type,
            );

            $record->update([
                'status' => 'done',
                'completed_at' => now(),
                'locked_by' => null,
                'input_tokens' => $result->inputTokens,
                'output_tokens' => $result->outputTokens,
                'cost_usd' => $result->costUsd,
            ]);
        } catch (AcordaoAnalysisPermanentException $exception) {
            $this->markAsError($record, $exception->getMessage());
        } catch (Throwable $exception) {
            $record->refresh();

            $message = AcordaoJobFailure::message($exception);

            if (AcordaoJobFailure::isPermanent($exception) || ! $record->canRetry()) {
                $this->markAsError($record, $message);

                return;
            }

            $record->update([
                'status' => 'queued',
                'last_error' => $message,
                'locked_by' => null,
            ]);

            $this->release($this->backoffSecondsForAttempt($record->attempts));
        }
    }

    public function failed(?Throwable $exception): void
    {
        $record = TeseAnalysisJob::query()->find($this->teseAnalysisJobId);

        if ($record === null || in_array($record->status, ['done', 'error'], true)) {
            return;
        }

        $this->markAsError($record, $exception?->getMessage() ?? 'Job falhou sem mensagem');
    }

    private function analysisTimeLimit(): int
    {
        return (int) config('services.openrouter.request_timeout', 120) + 180;
    }

    private function markAsError(TeseAnalysisJob $record, string $message): void
    {
        $record->update([
            'status' => 'error',
            'last_error' => $message,
            'completed_at' => now(),
            'locked_by' => null,
        ]);
    }

    private function queueWorkerId(): string
    {
        $uuid = $this->job?->uuid();

        if (is_string($uuid) && $uuid !== '') {
            return substr($uuid, 0, 50);
        }

        return substr((string) gethostname(), 0, 50);
    }

    private function backoffSecondsForAttempt(int $attempts): int
    {
        $index = max(0, $attempts - 1);

        return $this->backoff[$index] ?? end($this->backoff);
    }
}
