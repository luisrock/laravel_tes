<?php

use App\Models\AiModel;

it('persiste um ai_model com provider openrouter', function () {
    $model = AiModel::create([
        'provider' => 'openrouter',
        'name' => 'Claude Sonnet 4',
        'model_id' => 'anthropic/claude-sonnet-4',
        'price_input_per_million' => 3.0000,
        'price_output_per_million' => 15.0000,
        'is_active' => true,
    ]);

    expect($model->exists)->toBeTrue();

    expect(AiModel::query()->where('provider', 'openrouter')->where('model_id', 'anthropic/claude-sonnet-4')->exists())
        ->toBeTrue();

    expect($model->fresh()->provider)->toBe('openrouter');
});

it('mantem os providers originais aceitos', function () {
    foreach (['openai', 'anthropic', 'google'] as $provider) {
        $model = AiModel::create([
            'provider' => $provider,
            'name' => "Modelo {$provider}",
            'model_id' => "{$provider}/modelo-teste",
            'is_active' => true,
        ]);

        expect($model->fresh()->provider)->toBe($provider);
    }
});
