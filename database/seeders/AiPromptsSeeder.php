<?php

namespace Database\Seeders;

use App\Ai\Agents\StatsAnalyst;
use App\Models\AiPrompt;
use Illuminate\Database\Seeder;

/**
 * Semeia os prompts de IA editáveis. Idempotente: não sobrescreve conteúdo já editado pelo admin.
 *
 * Em produção precisa ser rodado manualmente (não está no script de deploy do Vito):
 * `php artisan db:seed --class=AiPromptsSeeder --force`. Sem o registro, o agente usa o fallback em código.
 */
class AiPromptsSeeder extends Seeder
{
    public function run(): void
    {
        AiPrompt::firstOrCreate(
            ['key' => StatsAnalyst::SYSTEM_PROMPT_KEY],
            [
                'title' => 'Analista de Estatísticas — system prompt',
                'content' => StatsAnalyst::defaultInstructions(),
                'description' => 'Instruções (system prompt) do assistente de IA da página de Estatísticas.',
            ]
        );

        AiPrompt::firstOrCreate(
            ['key' => StatsAnalyst::EVALUATE_PROMPT_KEY],
            [
                'title' => 'Analista de Estatísticas — botão "Avaliar estatísticas"',
                'content' => StatsAnalyst::defaultEvaluatePrompt(),
                'description' => 'Prompt do botão "Avaliar estatísticas". Use {periodo} onde o rótulo do '
                    .'período selecionado (ex.: "Últimos 7 dias") deve ser inserido.',
            ]
        );
    }
}
