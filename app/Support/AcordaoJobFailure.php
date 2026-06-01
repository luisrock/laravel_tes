<?php

namespace App\Support;

use App\Exceptions\AcordaoAnalysisPermanentException;
use Throwable;

/**
 * Classifica falhas do job de análise de acórdãos (permanente vs retryável).
 */
final class AcordaoJobFailure
{
    /**
     * Erros que não devem voltar para queued (ex.: 400 context length / PDF grande).
     */
    public static function isPermanent(Throwable $exception): bool
    {
        if ($exception instanceof AcordaoAnalysisPermanentException) {
            return true;
        }

        $message = $exception->getMessage();

        if (preg_match('/OpenRouter Error: \[(4\d{2})\]/', $message) === 1) {
            return true;
        }

        $lower = mb_strtolower($message);

        foreach ([
            'context length',
            'maximum context',
            'max context',
            'token limit',
            'too many tokens',
            'maximum tokens',
            'payload too large',
            'request too large',
            'provider returned error',
            'invalid request',
        ] as $phrase) {
            if (str_contains($lower, $phrase)) {
                return true;
            }
        }

        return false;
    }

    public static function message(Throwable $exception): string
    {
        return $exception->getMessage();
    }
}
