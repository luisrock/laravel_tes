<?php

namespace Database\Seeders;

use App\Models\AiModel;
use Illuminate\Database\Seeder;

class AiModelsSeeder extends Seeder
{
    public function run()
    {
        $models = [
            [
                'provider' => 'anthropic',
                'name' => 'Claude Opus 4.5',
                'model_id' => 'claude-4.5-opus-20260128',
                'price_input_per_million' => 5.00,
                'price_output_per_million' => 25.00,
            ],
            [
                'provider' => 'openai',
                'name' => 'GPT-5.2',
                'model_id' => 'gpt-5.2',
                'price_input_per_million' => 1.75,
                'price_output_per_million' => 14.00,
            ],
            [
                'provider' => 'google',
                'name' => 'Gemini 3 Pro',
                'model_id' => 'gemini-3-pro',
                'price_input_per_million' => 2.00,
                'price_output_per_million' => 12.00,
            ],
            [
                'provider' => 'google',
                'name' => 'Gemini 3 Flash',
                'model_id' => 'gemini-3-flash',
                'price_input_per_million' => 0.50,
                'price_output_per_million' => 3.00,
            ],
        ];

        foreach ($models as $model) {
            AiModel::updateOrCreate(
                ['provider' => $model['provider'], 'model_id' => $model['model_id']],
                $model
            );
        }

        $this->command->info('Seeded '.count($models).' AI models.');
    }
}
