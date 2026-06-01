<?php

namespace App\Services\Ai;

/**
 * Resultado da execução do AcordaoAnalysisService.
 */
final class AcordaoAnalysisResult
{
    public function __construct(
        public readonly bool $skippedDueToIdempotency,
        public readonly int $sectionsCreated,
        public readonly int $inputTokens,
        public readonly int $outputTokens,
        public readonly float $costUsd,
    ) {}

    public static function idempotentSkip(): self
    {
        return new self(true, 0, 0, 0, 0.0);
    }
}
