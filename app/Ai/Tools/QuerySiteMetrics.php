<?php

namespace App\Ai\Tools;

use App\Services\Newsletter\SiteMetrics;
use App\Services\Sendy\SendyService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

/**
 * Tool do AI SDK: expõe ao modelo as métricas do site (registos, inscrições na newsletter por fonte,
 * total na lista de email e conversão do popup) para um período em dias.
 *
 * Reaproveita exatamente os cálculos de SiteMetrics + SendyService usados pelo widget SiteOverviewStats.
 */
class QuerySiteMetrics implements Tool
{
    /**
     * Períodos suportados, em dias (alinhados a SiteMetrics::PERIOD_OPTIONS).
     *
     * @var array<int, int>
     */
    private const ALLOWED_PERIODS = [1, 3, 7, 30, 60];

    public function description(): Stringable|string
    {
        return 'Consulta as métricas do site Teses & Súmulas para um período em dias '
            .'(valores aceitos: 1, 3, 7, 30 ou 60). Retorna novos registos, novas inscrições na '
            .'newsletter (total e via páginas de newsletters), total de contas inscritas, total na '
            .'lista de email (Sendy) e taxa de conversão do popup. Use esta tool para obter números '
            .'reais antes de analisar a evolução das estatísticas.';
    }

    public function handle(Request $request): Stringable|string
    {
        $period = $this->normalizePeriod($request['period'] ?? null);

        $metrics = [
            'periodo_dias' => (int) $period,
            'periodo_label' => SiteMetrics::periodLabel($period),
            'novos_registos' => SiteMetrics::newUserRegistrations($period),
            'novas_inscricoes_newsletter' => SiteMetrics::newSubscriptions($period),
            'inscricoes_via_paginas_newsletters' => SiteMetrics::newsletterPagesSubscriptions($period),
            'contas_inscritas_no_site' => SiteMetrics::cachedSubscribedUserCount(),
            'total_na_lista_de_email' => app(SendyService::class)->activeSubscriberCount(),
            'conversao_popup_percent' => SiteMetrics::popupConversionRate($period),
            'inscricoes_por_fonte' => SiteMetrics::subscriptionsBySource($period),
        ];

        return (string) json_encode($metrics, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'period' => $schema->integer()
                ->description('Período em dias. Valores aceitos: 1, 3, 7, 30 ou 60.')
                ->required(),
        ];
    }

    private function normalizePeriod(mixed $period): string
    {
        $value = (int) $period;

        return in_array($value, self::ALLOWED_PERIODS, true) ? (string) $value : '30';
    }
}
