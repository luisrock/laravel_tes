<?php

namespace App\Services\Ai;

use App\Models\AiModel;

/**
 * Resolve um slug OpenRouter para uma linha de `ai_models` (FK de custo dos jobs/seções).
 *
 * Faz upsert por (`provider='openrouter'`, `model_id=$slug`): cria a linha com nome/preços do
 * catálogo OpenRouter ou, quando já existe, sincroniza nome e preços com o catálogo atual
 * (política de atualização confirmada com o usuário). Não altera `is_active` de linhas existentes,
 * preservando desativações manuais.
 */
class AiModelResolver
{
    public function __construct(private OpenRouterManagementService $openRouter) {}

    /**
     * Resolve o slug para a linha `ai_models` correspondente, criando ou atualizando conforme o catálogo.
     */
    public function resolveOpenRouterModel(string $slug): AiModel
    {
        $model = AiModel::firstOrNew([
            'provider' => 'openrouter',
            'model_id' => $slug,
        ]);

        $isNew = ! $model->exists;

        if ($isNew) {
            $model->is_active = true;
        }

        $name = $this->openRouter->modelName($slug);

        if ($name !== null) {
            $model->name = $name;
        } elseif ($isNew) {
            $model->name = $slug;
        }

        $pricing = $this->openRouter->modelPricingPerMillion($slug);

        if ($pricing !== null) {
            $model->price_input_per_million = $pricing['input'];
            $model->price_output_per_million = $pricing['output'];
        }

        $model->save();

        return $model;
    }
}
