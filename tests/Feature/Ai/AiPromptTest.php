<?php

use App\Ai\Agents\StatsAnalyst;
use App\Models\AiPrompt;
use Database\Seeders\AiPromptsSeeder;

describe('AiPrompt — modelo', function () {

    it('persiste e lê o conteúdo por key', function () {
        AiPrompt::factory()->create([
            'key' => 'stats_analyst_system',
            'content' => 'Prompt customizado.',
        ]);

        expect(AiPrompt::contentForKey('stats_analyst_system'))->toBe('Prompt customizado.');
    });

    it('retorna null quando a key não existe', function () {
        expect(AiPrompt::contentForKey('inexistente'))->toBeNull();
    });

});

describe('StatsAnalyst::instructions — prompt editável com fallback', function () {

    it('usa o conteúdo do AiPrompt quando presente', function () {
        AiPrompt::factory()->create([
            'key' => StatsAnalyst::SYSTEM_PROMPT_KEY,
            'content' => 'Instruções editadas pelo admin.',
        ]);

        expect((string) (new StatsAnalyst)->instructions())->toBe('Instruções editadas pelo admin.');
    });

    it('cai no texto default quando não há registro', function () {
        expect((string) (new StatsAnalyst)->instructions())
            ->toBe(StatsAnalyst::defaultInstructions())
            ->toContain('analista de dados');
    });

    it('cai no texto default quando o conteúdo está vazio', function () {
        AiPrompt::factory()->create([
            'key' => StatsAnalyst::SYSTEM_PROMPT_KEY,
            'content' => '   ',
        ]);

        expect((string) (new StatsAnalyst)->instructions())->toBe(StatsAnalyst::defaultInstructions());
    });

});

describe('AiPromptsSeeder', function () {

    it('semeia o prompt do StatsAnalyst com o texto default', function () {
        (new AiPromptsSeeder)->run();

        $prompt = AiPrompt::where('key', StatsAnalyst::SYSTEM_PROMPT_KEY)->first();

        expect($prompt)->not->toBeNull()
            ->and($prompt->content)->toBe(StatsAnalyst::defaultInstructions());
    });

    it('é idempotente e não sobrescreve conteúdo editado', function () {
        AiPrompt::factory()->create([
            'key' => StatsAnalyst::SYSTEM_PROMPT_KEY,
            'content' => 'Editado.',
        ]);

        (new AiPromptsSeeder)->run();

        expect(AiPrompt::where('key', StatsAnalyst::SYSTEM_PROMPT_KEY)->count())->toBe(1)
            ->and(AiPrompt::contentForKey(StatsAnalyst::SYSTEM_PROMPT_KEY))->toBe('Editado.');
    });

});
