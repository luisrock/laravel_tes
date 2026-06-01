<?php

use App\Models\AiPrompt;
use Database\Seeders\AiPromptsSeeder;

it('seeds a non-empty acordao analysis system prompt', function () {
    $this->seed(AiPromptsSeeder::class);

    $content = AiPrompt::contentForKey('acordao_analysis_system');

    expect($content)->toBeString()
        ->and(trim((string) $content))->not->toBe('')
        ->and($content)->toContain('{tema}')
        ->and($content)->toContain('{texto_tema}')
        ->and($content)->toContain('{texto_tese}')
        ->and($content)->toContain('RETORNE EXCLUSIVAMENTE um JSON');
});

it('is idempotent and does not duplicate the prompt', function () {
    $this->seed(AiPromptsSeeder::class);
    $this->seed(AiPromptsSeeder::class);

    expect(AiPrompt::query()->where('key', 'acordao_analysis_system')->count())->toBe(1);
});

it('does not overwrite an admin-edited prompt', function () {
    AiPrompt::create([
        'key' => 'acordao_analysis_system',
        'title' => 'Custom',
        'content' => 'Conteúdo editado pelo admin',
        'description' => 'editado',
    ]);

    $this->seed(AiPromptsSeeder::class);

    expect(AiPrompt::contentForKey('acordao_analysis_system'))->toBe('Conteúdo editado pelo admin');
});
