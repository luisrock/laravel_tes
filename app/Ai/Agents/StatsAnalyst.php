<?php

namespace App\Ai\Agents;

use App\Ai\Tools\QuerySiteMetrics;
use App\Models\AiPrompt;
use App\Models\SiteSetting;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use RuntimeException;
use Stringable;

/**
 * Agente analista das estatísticas do site (admin). Usa o provedor OpenRouter e o modelo escolhido em
 * Configurações de IA (SiteSetting `ai_chat_model`), conversando sobre as métricas via a tool QuerySiteMetrics.
 *
 * A conversa é persistida via o trait RemembersConversations do SDK: `forUser()` inicia uma nova conversa
 * e `continue()` retoma uma existente; o histórico (`messages()`) vem do ConversationStore quando há
 * participante. Sem participante (ex.: testes unitários do agente), comporta-se como uma chamada avulsa.
 */
class StatsAnalyst implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Key do registro `AiPrompt` que guarda o system prompt deste agente.
     */
    public const SYSTEM_PROMPT_KEY = 'stats_analyst_system';

    /**
     * Key do registro `AiPrompt` que guarda o prompt do botão "Avaliar estatísticas".
     * O placeholder `{periodo}` é substituído pelo rótulo do período selecionado.
     */
    public const EVALUATE_PROMPT_KEY = 'stats_analyst_evaluate';

    /**
     * Lê o system prompt do registro `AiPrompt` editável; cai no texto default se ausente ou vazio.
     */
    public function instructions(): Stringable|string
    {
        $content = AiPrompt::contentForKey(self::SYSTEM_PROMPT_KEY);

        if (is_string($content) && trim($content) !== '') {
            return $content;
        }

        return self::defaultInstructions();
    }

    /**
     * System prompt padrão (fallback) — também usado para semear o registro editável.
     */
    public static function defaultInstructions(): string
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
     * Monta o prompt do botão "Avaliar estatísticas" para o período informado, lendo o registro
     * `AiPrompt` editável (com fallback ao texto padrão). O placeholder `{periodo}` é substituído
     * pelo rótulo do período (ex.: "Últimos 7 dias").
     */
    public static function evaluatePromptFor(string $periodLabel): string
    {
        $template = AiPrompt::contentForKey(self::EVALUATE_PROMPT_KEY);

        if (! is_string($template) || trim($template) === '') {
            $template = self::defaultEvaluatePrompt();
        }

        return str_replace('{periodo}', $periodLabel, $template);
    }

    /**
     * Texto padrão (fallback) do prompt do botão de avaliação — também usado para semear o registro.
     */
    public static function defaultEvaluatePrompt(): string
    {
        return 'Faça uma avaliação das estatísticas do período "{periodo}". Consulte os números, aponte as '
            .'principais tendências, possíveis causas e recomendações práticas para melhorar registos '
            .'e inscrições na newsletter.';
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
