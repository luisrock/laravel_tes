<?php

use App\Support\SectionQa;

function qaPad(string $prefix, int $minLength): string
{
    $content = $prefix;

    while (mb_strlen($content) < $minLength) {
        $content .= 'x';
    }

    return $content;
}

function validTeaser(): string
{
    return qaPad('Tema 1069 do STJ: entendimento sobre tributação e recursos repetitivos. ', 200);
}

it('passes a valid teaser mentioning tribunal and tema', function () {
    $result = SectionQa::validateTeaser(validTeaser(), 'STJ', 1069);

    expect($result['ok'])->toBeTrue()
        ->and($result['message'])->toBe('OK');
});

it('maps a passing teaser to published status', function () {
    expect(SectionQa::teaserStatus(validTeaser(), 'STJ', 1069))->toBe('published');
});

it('maps a failing teaser to draft status', function () {
    expect(SectionQa::teaserStatus('curto', 'STJ', 1069))->toBe('draft');
});

it('rejects teaser below minimum length', function () {
    $result = SectionQa::validateTeaser('Tema 1069 do STJ.', 'STJ', 1069);

    expect($result['ok'])->toBeFalse()
        ->and($result['message'])->toContain('Muito curto');
});

it('rejects teaser above maximum length', function () {
    $result = SectionQa::validateTeaser(qaPad('Tema 1069 do STJ. ', 1201), 'STJ', 1069);

    expect($result['ok'])->toBeFalse()
        ->and($result['message'])->toContain('Muito longo');
});

it('rejects teaser without tribunal or tema mention', function () {
    $result = SectionQa::validateTeaser(qaPad('Resumo genérico sem referência ao tribunal. ', 200), 'STJ', 1069);

    expect($result['ok'])->toBeFalse()
        ->and($result['message'])->toBe('Não menciona tribunal/tema');
});

it('accepts teaser mentioning repercussão geral without tema number', function () {
    $result = SectionQa::validateTeaser(
        qaPad('O STF fixou entendimento em repercussão geral sobre matéria tributária. ', 200),
        'STF',
        1069
    );

    expect($result['ok'])->toBeTrue();
});

it('rejects forbidden phrases in teaser', function (string $phrase) {
    $result = SectionQa::validateTeaser(
        qaPad("Tema 1069 do STJ. {$phrase}. ", 200),
        'STJ',
        1069
    );

    expect($result['ok'])->toBeFalse()
        ->and($result['message'])->toBe("Frase proibida: '{$phrase}'");
})->with(fn () => SectionQa::FORBIDDEN_PHRASES);

it('validates section length limits', function (string $sectionType, int $min, int $max) {
    $prefix = match ($sectionType) {
        'modulacao' => 'Modulação aplicada pelo tribunal em julgamento recente. ',
        default => 'Conteúdo jurídico válido para a seção. ',
    };

    $tooShort = SectionQa::validateSection(qaPad($prefix, $min - 1), $sectionType);
    expect($tooShort['ok'])->toBeFalse()
        ->and($tooShort['message'])->toContain('Muito curto');

    $valid = SectionQa::validateSection(qaPad($prefix, $min), $sectionType);
    expect($valid['ok'])->toBeTrue();

    $tooLong = SectionQa::validateSection(qaPad($prefix, $max + 1), $sectionType);
    expect($tooLong['ok'])->toBeFalse()
        ->and($tooLong['message'])->toContain('Muito longo');
})->with([
    ['teaser', 200, 1200],
    ['caso_fatico', 600, 4000],
    ['contornos_juridicos', 800, 6000],
    ['modulacao', 60, 2500],
    ['tese_explicada', 800, 5000],
]);

it('accepts default modulation phrases below minimum length', function (string $phrase) {
    $result = SectionQa::validateSection($phrase, 'modulacao');

    expect($result['ok'])->toBeTrue();
})->with(fn () => SectionQa::MODULATION_DEFAULT_PHRASES);

it('accepts missing-info phrase below minimum length', function (string $sectionType) {
    $result = SectionQa::validateSection(SectionQa::MISSING_INFO_PHRASE, $sectionType);

    expect($result['ok'])->toBeTrue();
})->with(array_keys(SectionQa::SECTION_LIMITS));

it('rejects forbidden phrases in generic sections', function (string $phrase) {
    $content = qaPad("Tema 1069 do STJ. {$phrase}. ", 800);

    $result = SectionQa::validateSection($content, 'caso_fatico');

    expect($result['ok'])->toBeFalse()
        ->and($result['message'])->toBe("Frase proibida: '{$phrase}'");
})->with(fn () => SectionQa::FORBIDDEN_PHRASES);

it('skips validation for unknown section types', function () {
    $result = SectionQa::validateSection('qualquer coisa', 'desconhecida');

    expect($result['ok'])->toBeTrue();
});
