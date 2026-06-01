<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Consultas de gerenciamento à conta OpenRouter (crédito residual e catálogo de modelos).
 *
 * Isolada das features de IA existentes ("Decifrando a Tese"). Usa a chave de gerenciamento
 * (`services.openrouter.management_key`), distinta da chave de requisições ao modelo.
 */
final class OpenRouterManagementService
{
    private const MODELS_CACHE_KEY = 'openrouter:models';

    private const RAW_MODELS_CACHE_KEY = 'openrouter:models:raw';

    private const MODELS_CACHE_TTL = 21600; // 6 horas

    public function __construct(private HttpFactory $http) {}

    /**
     * Crédito residual da conta (total comprado menos total usado), em USD.
     *
     * Retorna null quando a chave está ausente ou a API falha.
     */
    public function remainingCredits(): ?float
    {
        $key = $this->managementKey();

        if ($key === null) {
            return null;
        }

        try {
            $response = $this->http
                ->withToken($key)
                ->get($this->apiUrl('/credits'));

            if ($response->failed()) {
                Log::warning('OpenRouter credits request failed', ['status' => $response->status()]);

                return null;
            }

            $data = $response->json('data', []);

            if (! isset($data['total_credits'], $data['total_usage'])) {
                return null;
            }

            return round((float) $data['total_credits'] - (float) $data['total_usage'], 4);
        } catch (Throwable $e) {
            Log::warning('OpenRouter credits request threw', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Catálogo de modelos de texto/chat disponíveis, como opções de Select (id => rótulo).
     *
     * Resultado é cacheado por 6h. Retorna array vazio em falha.
     *
     * @return array<string, string>
     */
    public function availableModels(): array
    {
        return Cache::remember(self::MODELS_CACHE_KEY, self::MODELS_CACHE_TTL, function (): array {
            return $this->fetchModels();
        });
    }

    /**
     * Modelos PDF-capable (aceitam arquivo como anexo), para a análise de acórdãos.
     *
     * Filtra o catálogo por modelos de texto cujo `architecture.input_modalities` inclui `file`.
     *
     * @return array<string, string>
     */
    public function pdfCapableModels(): array
    {
        $models = [];

        foreach ($this->rawCatalog() as $id => $model) {
            if (! $this->isTextModel($model) || ! $this->isPdfCapable($model)) {
                continue;
            }

            $models[$id] = $this->formatModelLabel($model);
        }

        asort($models);

        return $models;
    }

    /**
     * Limpa o cache do catálogo de modelos (usado pelo botão "Atualizar").
     */
    public function clearModelsCache(): void
    {
        Cache::forget(self::MODELS_CACHE_KEY);
        Cache::forget(self::RAW_MODELS_CACHE_KEY);
    }

    /**
     * Preços por 1M de tokens (input/output) de um modelo do catálogo, prontos para `ai_models`.
     *
     * Retorna null quando o slug não existe no catálogo ou não tem pricing de texto.
     *
     * @return array{input: float, output: float}|null
     */
    public function modelPricingPerMillion(string $slug): ?array
    {
        $model = $this->rawModel($slug);

        if ($model === null) {
            return null;
        }

        $prompt = $model['pricing']['prompt'] ?? null;
        $completion = $model['pricing']['completion'] ?? null;

        if (! is_numeric($prompt) || ! is_numeric($completion)) {
            return null;
        }

        return [
            'input' => round((float) $prompt * 1_000_000, 4),
            'output' => round((float) $completion * 1_000_000, 4),
        ];
    }

    /**
     * Nome legível do modelo no catálogo (ex.: "Anthropic: Claude Sonnet 4.6"); null se ausente.
     */
    public function modelName(string $slug): ?string
    {
        $model = $this->rawModel($slug);
        $name = $model['name'] ?? null;

        return is_string($name) && $name !== '' ? $name : null;
    }

    /**
     * @return array<string, string>
     */
    private function fetchModels(): array
    {
        $models = [];

        foreach ($this->rawCatalog() as $id => $model) {
            if (! $this->isTextModel($model)) {
                continue;
            }

            $models[$id] = $this->formatModelLabel($model);
        }

        asort($models);

        return $models;
    }

    /**
     * Registro bruto de um modelo do catálogo OpenRouter por slug.
     *
     * @return array<string, mixed>|null
     */
    private function rawModel(string $slug): ?array
    {
        return $this->rawCatalog()[$slug] ?? null;
    }

    /**
     * Catálogo bruto da API OpenRouter, indexado por slug e cacheado por 6h.
     *
     * @return array<string, array<string, mixed>>
     */
    private function rawCatalog(): array
    {
        return Cache::remember(self::RAW_MODELS_CACHE_KEY, self::MODELS_CACHE_TTL, function (): array {
            return $this->fetchRawCatalog();
        });
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function fetchRawCatalog(): array
    {
        $key = $this->managementKey();

        if ($key === null) {
            return [];
        }

        try {
            $response = $this->http
                ->withToken($key)
                ->get($this->apiUrl('/models'));

            if ($response->failed()) {
                Log::warning('OpenRouter models request failed', ['status' => $response->status()]);

                return [];
            }

            $catalog = [];

            foreach ($response->json('data', []) as $model) {
                if (is_array($model) && isset($model['id']) && is_string($model['id'])) {
                    $catalog[$model['id']] = $model;
                }
            }

            return $catalog;
        } catch (Throwable $e) {
            Log::warning('OpenRouter models request threw', ['message' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Mantém apenas modelos capazes de gerar texto (prompt + completion no pricing e texto nas modalidades).
     *
     * @param  array<string, mixed>  $model
     */
    private function isTextModel(array $model): bool
    {
        $pricing = $model['pricing'] ?? [];

        if (! isset($pricing['prompt'], $pricing['completion'])) {
            return false;
        }

        $outputModalities = $model['architecture']['output_modalities'] ?? ['text'];

        return in_array('text', (array) $outputModalities, true);
    }

    /**
     * Indica se o modelo aceita PDF/arquivo como anexo de entrada (multimodal).
     *
     * @param  array<string, mixed>  $model
     */
    private function isPdfCapable(array $model): bool
    {
        $inputModalities = $model['architecture']['input_modalities'] ?? [];

        return in_array('file', (array) $inputModalities, true);
    }

    /**
     * Rótulo: "Nome ($X/M in · $Y/M out · 200K ctx)".
     *
     * @param  array<string, mixed>  $model
     */
    private function formatModelLabel(array $model): string
    {
        $name = $model['name'] ?? $model['id'];
        $parts = [];

        $in = $this->pricePerMillion($model['pricing']['prompt'] ?? null);
        if ($in !== null) {
            $parts[] = '$'.$in.'/M in';
        }

        $out = $this->pricePerMillion($model['pricing']['completion'] ?? null);
        if ($out !== null) {
            $parts[] = '$'.$out.'/M out';
        }

        $context = $model['context_length'] ?? ($model['top_provider']['context_length'] ?? null);
        if (is_numeric($context) && (int) $context > 0) {
            $parts[] = $this->formatContextLength((int) $context);
        }

        return $parts === [] ? $name : sprintf('%s (%s)', $name, implode(' · ', $parts));
    }

    /**
     * Converte o preço por token (string OpenRouter) em preço por milhão, sem zeros à direita.
     */
    private function pricePerMillion(mixed $price): ?string
    {
        if ($price === null || ! is_numeric($price)) {
            return null;
        }

        $perMillion = (float) $price * 1_000_000;

        if ($perMillion === 0.0) {
            return '0';
        }

        return rtrim(rtrim(number_format($perMillion, 4, '.', ''), '0'), '.');
    }

    private function formatContextLength(int $context): string
    {
        if ($context >= 1_000_000) {
            return rtrim(rtrim(number_format($context / 1_000_000, 1, '.', ''), '0'), '.').'M';
        }

        if ($context >= 1_000) {
            return (string) intdiv($context, 1_000).'K';
        }

        return (string) $context;
    }

    private function apiUrl(string $path): string
    {
        return rtrim((string) config('services.openrouter.base_url'), '/').$path;
    }

    private function managementKey(): ?string
    {
        $key = config('services.openrouter.management_key');

        return is_string($key) && $key !== '' ? $key : null;
    }
}
