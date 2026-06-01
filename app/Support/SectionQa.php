<?php

namespace App\Support;

/**
 * Quality Assurance das seções geradas pela IA ("Decifrando a Tese").
 *
 * Porte fiel de qa.py do script Python tesacordaos_ia.
 */
final class SectionQa
{
    /** @var list<string> */
    public const FORBIDDEN_PHRASES = [
        'como IA',
        'como modelo de linguagem',
        'não tenho acesso',
        'não posso acessar',
        'baseado no texto fornecido',
        'como assistente',
        'como uma IA',
        'não possuo acesso',
        'não é possível acessar',
    ];

    /** @var array<string, array{0: int, 1: int}> */
    public const SECTION_LIMITS = [
        'teaser' => [200, 1200],
        'caso_fatico' => [600, 4000],
        'contornos_juridicos' => [800, 6000],
        'modulacao' => [60, 2500],
        'tese_explicada' => [800, 5000],
    ];

    /** @var list<string> */
    public const MODULATION_DEFAULT_PHRASES = [
        'Não houve modulação de efeitos neste julgamento.',
        'O acórdão não aborda modulação de efeitos.',
    ];

    public const MISSING_INFO_PHRASE = 'Não consta informação suficiente no acórdão.';

    /**
     * Valida o teaser (limites, frases proibidas, menção a tribunal/tema).
     *
     * @return array{ok: bool, message: string}
     */
    public static function validateTeaser(string $content, string $tribunal, int $temaNumero): array
    {
        $lengthFailure = self::failureForLength('teaser', $content);

        if ($lengthFailure !== null) {
            return $lengthFailure;
        }

        $forbiddenFailure = self::failureForForbiddenPhrases($content);

        if ($forbiddenFailure !== null) {
            return $forbiddenFailure;
        }

        if (! self::mentionsTribunalOrTema($content, $tribunal, $temaNumero)) {
            return self::fail('Não menciona tribunal/tema');
        }

        return self::ok();
    }

    /**
     * Valida uma seção genérica (exceto teaser, que usa validateTeaser).
     *
     * @return array{ok: bool, message: string}
     */
    public static function validateSection(string $content, string $sectionType): array
    {
        if (! isset(self::SECTION_LIMITS[$sectionType])) {
            return self::ok();
        }

        if ($sectionType === 'modulacao' && in_array(trim($content), self::MODULATION_DEFAULT_PHRASES, true)) {
            return self::ok();
        }

        [$minLength] = self::SECTION_LIMITS[$sectionType];

        if (str_contains($content, self::MISSING_INFO_PHRASE) && self::length($content) < $minLength) {
            return self::ok();
        }

        $lengthFailure = self::failureForLength($sectionType, $content);

        if ($lengthFailure !== null) {
            return $lengthFailure;
        }

        $forbiddenFailure = self::failureForForbiddenPhrases($content);

        return $forbiddenFailure ?? self::ok();
    }

    /**
     * Status recomendado para o teaser após QA (paridade com process_job do Python).
     */
    public static function teaserStatus(string $content, string $tribunal, int $temaNumero): string
    {
        return self::validateTeaser($content, $tribunal, $temaNumero)['ok'] ? 'published' : 'draft';
    }

    /**
     * @return array{ok: bool, message: string}|null
     */
    private static function failureForLength(string $sectionType, string $content): ?array
    {
        [$minLength, $maxLength] = self::SECTION_LIMITS[$sectionType];
        $length = self::length($content);

        if ($length < $minLength) {
            return self::fail("Muito curto ({$length} < {$minLength} chars)");
        }

        if ($length > $maxLength) {
            return self::fail("Muito longo ({$length} > {$maxLength} chars)");
        }

        return null;
    }

    /**
     * @return array{ok: bool, message: string}|null
     */
    private static function failureForForbiddenPhrases(string $content): ?array
    {
        $contentLower = mb_strtolower($content);

        foreach (self::FORBIDDEN_PHRASES as $phrase) {
            if (str_contains($contentLower, mb_strtolower($phrase))) {
                return self::fail("Frase proibida: '{$phrase}'");
            }
        }

        return null;
    }

    private static function mentionsTribunalOrTema(string $content, string $tribunal, int $temaNumero): bool
    {
        $contentLower = mb_strtolower($content);

        $validTerms = [
            mb_strtoupper($tribunal),
            "Tema {$temaNumero}",
            'repercussão geral',
            'recursos repetitivos',
        ];

        foreach ($validTerms as $term) {
            if (str_contains($contentLower, mb_strtolower($term))) {
                return true;
            }
        }

        return false;
    }

    private static function length(string $content): int
    {
        return mb_strlen($content);
    }

    /**
     * @return array{ok: bool, message: string}
     */
    private static function ok(): array
    {
        return ['ok' => true, 'message' => 'OK'];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    private static function fail(string $message): array
    {
        return ['ok' => false, 'message' => $message];
    }
}
