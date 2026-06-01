<?php

namespace App\Services\Ai;

use App\Ai\Agents\AcordaoAnalyst;
use App\Exceptions\AcordaoAnalysisPermanentException;
use App\Models\AiModel;
use App\Models\TeseAcordao;
use App\Models\TeseAnalysisSection;
use App\Support\SectionQa;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Files\Document;
use Laravel\Ai\Responses\StructuredAgentResponse;

/**
 * Orquestra a geração das análises de acórdãos ("Decifrando a Tese") via AcordaoAnalyst.
 */
class AcordaoAnalysisService
{
    /** @var list<string> */
    public const SECTION_TYPES = [
        'teaser',
        'caso_fatico',
        'contornos_juridicos',
        'modulacao',
        'tese_explicada',
    ];

    public function analyze(
        int $teseId,
        string $tribunal,
        AiModel $aiModel,
        string $jobSectionType = 'all',
    ): AcordaoAnalysisResult {
        $tribunal = strtoupper($tribunal);
        $tese = $this->loadTese($teseId, $tribunal);
        $acordaos = $this->loadAcordaos($teseId, $tribunal);

        if ($acordaos->isEmpty()) {
            throw new AcordaoAnalysisPermanentException('Nenhum acórdão encontrado');
        }

        $promptHash = hash('sha256', AcordaoAnalyst::promptTemplate());
        $sourceHash = $this->computeSourceHash($acordaos);
        $sectionsToGenerate = $this->sectionsForJob($jobSectionType);

        $sectionsNeeded = array_values(array_filter(
            $sectionsToGenerate,
            fn (string $sectionType): bool => ! $this->sectionExists(
                $teseId,
                $tribunal,
                $sectionType,
                $sourceHash,
                $promptHash,
                $aiModel->id,
            ),
        ));

        if ($sectionsNeeded === []) {
            return AcordaoAnalysisResult::idempotentSkip();
        }

        $attachments = $this->buildAttachments($acordaos);
        $promptReplacements = [
            'tema' => (string) $this->temaNumero($tese),
            'tribunal' => $tribunal,
            'texto_tema' => $this->temaTexto($tese, $tribunal),
            'texto_tese' => $this->teseTexto($tese),
        ];

        $agent = new AcordaoAnalyst($aiModel->model_id, $promptReplacements);

        /** @var StructuredAgentResponse $response */
        $response = $agent->prompt(
            'Proceda com a análise dos acórdãos PDF anexados conforme as instruções.',
            $attachments,
            model: $aiModel->model_id,
        );

        $structured = $response->structured;
        $erro = $structured['erro'] ?? null;

        if (is_string($erro) && trim($erro) !== '') {
            throw new AcordaoAnalysisPermanentException("Erro reportado pela IA: {$erro}");
        }

        $inputTokens = $response->usage->promptTokens;
        $outputTokens = $response->usage->completionTokens;
        $totalCost = $aiModel->calculateCost($inputTokens, $outputTokens);
        $sectionCount = count($sectionsNeeded);
        $tokensInputPerSection = intdiv($inputTokens, $sectionCount);
        $tokensOutputPerSection = intdiv($outputTokens, $sectionCount);
        $costPerSection = round($totalCost / $sectionCount, 6);

        $sectionsCreated = 0;

        foreach ($sectionsNeeded as $sectionType) {
            $content = trim((string) ($structured[$sectionType] ?? ''));

            if ($content === '') {
                continue;
            }

            $status = $sectionType === 'teaser'
                ? SectionQa::teaserStatus($content, $tribunal, $this->temaNumero($tese))
                : 'draft';

            TeseAnalysisSection::create([
                'tese_id' => $teseId,
                'tribunal' => $tribunal,
                'section_type' => $sectionType,
                'content' => $content,
                'status' => $status,
                'is_active' => false,
                'ai_model_id' => $aiModel->id,
                'prompt_key' => AcordaoAnalyst::SYSTEM_PROMPT_KEY,
                'prompt_hash' => $promptHash,
                'source_hash' => $sourceHash,
                'tokens_input' => $tokensInputPerSection,
                'tokens_output' => $tokensOutputPerSection,
                'cost_usd' => $costPerSection,
                'price_snapshot_input' => $aiModel->price_input_per_million,
                'price_snapshot_output' => $aiModel->price_output_per_million,
                'provider_request_id' => $response->invocationId,
                'latency_ms' => null,
                'finish_reason' => null,
                'raw_usage' => $response->usage->toArray(),
            ]);

            $sectionsCreated++;
        }

        return new AcordaoAnalysisResult(
            skippedDueToIdempotency: false,
            sectionsCreated: $sectionsCreated,
            inputTokens: $inputTokens,
            outputTokens: $outputTokens,
            costUsd: $totalCost,
        );
    }

    /**
     * @return list<string>
     */
    private function sectionsForJob(string $sectionType): array
    {
        if ($sectionType === 'all') {
            return self::SECTION_TYPES;
        }

        if (! in_array($sectionType, self::SECTION_TYPES, true)) {
            throw new AcordaoAnalysisPermanentException("Tipo de seção inválido: {$sectionType}");
        }

        return [$sectionType];
    }

    private function loadTese(int $teseId, string $tribunal): object
    {
        $table = $tribunal === 'STF' ? 'stf_teses' : 'stj_teses';
        $tese = DB::table($table)->where('id', $teseId)->first();

        if ($tese === null) {
            throw new AcordaoAnalysisPermanentException('Tese não encontrada');
        }

        return $tese;
    }

    /**
     * @return EloquentCollection<int, TeseAcordao>
     */
    private function loadAcordaos(int $teseId, string $tribunal): EloquentCollection
    {
        return TeseAcordao::query()
            ->forTese($teseId, $tribunal)
            ->whereNull('deleted_at')
            ->orderByRaw("CASE WHEN tipo = 'Principal' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  EloquentCollection<int, TeseAcordao>  $acordaos
     * @return list<Document>
     */
    private function buildAttachments(EloquentCollection $acordaos): array
    {
        $attachments = [];

        foreach ($acordaos as $acordao) {
            if (! is_string($acordao->s3_key) || $acordao->s3_key === '') {
                continue;
            }

            if (! Storage::disk('s3')->exists($acordao->s3_key)) {
                throw new AcordaoAnalysisPermanentException("PDF não encontrado no S3: {$acordao->s3_key}");
            }

            $attachments[] = Document::fromStorage($acordao->s3_key, 's3');
        }

        if ($attachments === []) {
            throw new AcordaoAnalysisPermanentException('Nenhum acórdão com PDF disponível no S3');
        }

        return $attachments;
    }

    /**
     * @param  EloquentCollection<int, TeseAcordao>  $acordaos
     */
    private function computeSourceHash(EloquentCollection $acordaos): string
    {
        /** @var Collection<int, string> $checksums */
        $checksums = $acordaos
            ->pluck('checksum')
            ->filter(fn (?string $checksum): bool => is_string($checksum) && $checksum !== '');

        return hash('sha256', $checksums->implode(''));
    }

    private function sectionExists(
        int $teseId,
        string $tribunal,
        string $sectionType,
        string $sourceHash,
        string $promptHash,
        int $aiModelId,
    ): bool {
        return TeseAnalysisSection::query()
            ->where('tese_id', $teseId)
            ->where('tribunal', $tribunal)
            ->where('section_type', $sectionType)
            ->where('source_hash', $sourceHash)
            ->where('prompt_hash', $promptHash)
            ->where('ai_model_id', $aiModelId)
            ->exists();
    }

    private function temaNumero(object $tese): int
    {
        return (int) $tese->numero;
    }

    private function temaTexto(object $tese, string $tribunal): string
    {
        if ($tribunal === 'STF') {
            return $tese->tema_texto ?? 'Não disponível';
        }

        return $tese->tema ?? 'Não disponível';
    }

    private function teseTexto(object $tese): string
    {
        return $tese->tese_texto ?? 'Não disponível';
    }
}
