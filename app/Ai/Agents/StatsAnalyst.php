<?php

namespace App\Ai\Agents;

use App\Ai\Tools\QuerySiteMetrics;
use App\Models\SiteSetting;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use RuntimeException;
use Stringable;

/**
 * Agente analista das estatísticas do site (admin). Usa o provedor OpenRouter e o modelo escolhido em
 * Configurações de IA (SiteSetting `ai_chat_model`), conversando sobre as métricas via a tool QuerySiteMetrics.
 *
 * Histórico é efêmero: recebido no construtor a partir do componente Livewire.
 */
class StatsAnalyst implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     */
    public function __construct(public array $history = []) {}

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
        Você é um analista de dados do site "Teses e Súmulas" (tesesesumulas.com.br), um motor de busca
        jurídica. Seu público é o administrador do site. Seu objetivo é ajudá-lo a entender a evolução das
        métricas (novos registos, inscrições na newsletter por fonte, total na lista de email e conversão
        do popup) e a decidir melhorias.

        Regras:
        - SEMPRE use a ferramenta de consulta de métricas para obter números reais antes de concluir.
          Nunca invente valores. Se precisar comparar períodos, consulte cada período.
        - Períodos disponíveis: 1, 3, 7, 30 e 60 dias.
        - Seja objetivo e acionável: aponte tendências, possíveis causas e recomendações práticas.
        - Quando um valor não estiver disponível (ex.: total na lista de email retornar nulo), diga isso
          explicitamente em vez de supor.
        - Responda sempre em português do Brasil, em tom profissional e conciso.
        PROMPT;
    }

    /**
     * @return Message[]
     */
    public function messages(): iterable
    {
        return collect($this->history)
            ->map(fn (array $message): Message => new Message($message['role'], $message['content']))
            ->all();
    }

    /**
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new QuerySiteMetrics,
        ];
    }

    public function provider(): string
    {
        return 'openrouter';
    }

    public function model(): string
    {
        $model = SiteSetting::get('ai_chat_model');

        if (! is_string($model) || $model === '') {
            throw new RuntimeException('Nenhum modelo de IA configurado. Defina-o em Configurações de IA.');
        }

        return $model;
    }

    /**
     * Timeout HTTP (segundos) das chamadas ao provedor. Acomoda o tool-calling de múltiplos passos.
     */
    public function timeout(): int
    {
        return (int) config('services.openrouter.request_timeout', 120);
    }

    /**
     * Limita as idas-e-voltas com a tool para evitar laços longos numa resposta síncrona.
     */
    public function maxSteps(): int
    {
        return 6;
    }

    /**
     * Indica se há modelo configurado para uso pelo chat (usado pelo guard do componente Livewire).
     */
    public static function isConfigured(): bool
    {
        $model = SiteSetting::get('ai_chat_model');

        return is_string($model) && $model !== '';
    }
}
