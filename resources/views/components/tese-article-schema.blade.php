@props([
    'label',
    'description',
    'tese',
    'tribunal',
    'aiSections',
    'aiGeneratedAt' => null,
])

@php
    $hasAiSections = isset($aiSections) && $aiSections->isNotEmpty();

    $formatSchemaDate = static function ($value): ?string {
        if (blank($value)) {
            return null;
        }

        try {
            return 
                \Illuminate\Support\Carbon::parse($value)->toAtomString();
        } catch (\Throwable $exception) {
            return null;
        }
    };

    $datePublished = match (strtoupper($tribunal)) {
        'STF' => data_get($tese, 'aprovadaEm'),
        'STJ' => data_get($tese, 'atualizadaEm'),
        'TST', 'TNU' => data_get($tese, 'julgadoEm'),
        default => null,
    };

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $label,
        'description' => $description,
        'author' => [
            '@type' => 'Organization',
            'name' => 'Teses & Súmulas',
            'url' => 'https://tesesesumulas.com.br',
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Teses & Súmulas',
        ],
        'mainEntityOfPage' => url()->current(),
        'isAccessibleForFree' => false,
        'hasPart' => [[
            '@type' => 'WebPageElement',
            'isAccessibleForFree' => false,
            'cssSelector' => '.premium-content-blur',
        ]],
    ];

    if ($formattedDatePublished = $formatSchemaDate($datePublished)) {
        $schema['datePublished'] = $formattedDatePublished;
    }

    if ($formattedDateModified = $formatSchemaDate($aiGeneratedAt)) {
        $schema['dateModified'] = $formattedDateModified;
    }
@endphp

@if($hasAiSections)
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
@endif