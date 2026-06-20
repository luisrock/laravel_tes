<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Leitura pública e enxuta do teor de súmulas/teses (e súmulas vinculantes do STF)
 * das tabelas legadas dos tribunais. Centraliza a resolução de tabela/coluna, os
 * campos extras (tema/tese/situação) e a URL canônica para que o endpoint público
 * (S6/S7) e a ampliação para a extensão Chrome (S9) reusem a mesma lógica.
 *
 * Cobertura: súmulas em STF/STJ/TST/TNU/CARF/CEJ; teses em STF/STJ/TST/TNU; e
 * súmula vinculante apenas no STF. FONAJE fica adiado de propósito (3 sub-bases
 * civ/cri/faz com numerações próprias tornam `/FONAJE/{numero}` ambíguo) e TCU
 * não usa banco (API externa).
 */
class TribunalContentReader
{
    /**
     * Coluna de teor por tipo de conteúdo e tribunal. Funciona como allowlist:
     * só os tribunais aqui listados têm teor público (FONAJE intencionalmente fora).
     *
     * @var array<string, array<string, string>>
     */
    private const TEXT_COLUMNS = [
        'sumula' => [
            'STF' => 'texto',
            'STJ' => 'texto',
            'TST' => 'texto',
            'TNU' => 'texto',
            'CARF' => 'texto',
            'CEJ' => 'texto',
        ],
        'tese' => [
            'STF' => 'tese_texto',
            'STJ' => 'tese_texto',
            'TST' => 'texto',
            'TNU' => 'tese',
        ],
        'sumula-vinculante' => [
            'STF' => 'texto',
        ],
    ];

    /**
     * Coluna que descreve o TEMA (a questão submetida) por tribunal, para teses.
     *
     * @var array<string, string>
     */
    private const TESE_TEMA_COLUMNS = [
        'STF' => 'tema_texto',
        'STJ' => 'tema',
        'TST' => 'tema',
        'TNU' => 'tema',
    ];

    /**
     * Coluna de situação/status por tribunal, para teses. TST não possui.
     *
     * @var array<string, string>
     */
    private const TESE_SITUACAO_COLUMNS = [
        'STF' => 'situacao',
        'STJ' => 'situacao',
        'TNU' => 'situacao',
    ];

    /**
     * Tribunais que possuem página individual de teor (para URL canônica).
     *
     * @var array<int, string>
     */
    private const INDIVIDUAL_PAGE_TRIBUNALS = ['STF', 'STJ', 'TST', 'TNU'];

    public function __construct(private SearchTribunalRegistry $registry) {}

    /**
     * Indica se o teor está disponível para o tipo/tribunal informados.
     */
    public function supports(string $tipo, string $tribunalUpper): bool
    {
        return isset(self::TEXT_COLUMNS[$tipo][strtoupper($tribunalUpper)]);
    }

    /**
     * Busca o teor enxuto por número.
     *
     * Para súmulas/súmulas vinculantes retorna:
     *   { tribunal, tipo, numero, texto, situacao, url }
     * Para teses retorna (mantendo `texto` por compatibilidade):
     *   { tribunal, tipo, numero, texto, tema, tese, situacao, url }
     *
     * @return array<string, int|string>|null
     */
    public function find(string $tipo, string $tribunalUpper, int $numero): ?array
    {
        $tribunalUpper = strtoupper($tribunalUpper);

        if (! $this->supports($tipo, $tribunalUpper)) {
            return null;
        }

        $textColumn = self::TEXT_COLUMNS[$tipo][$tribunalUpper];
        $table = $this->tableFor($tipo, $tribunalUpper);

        $columns = array_values(array_unique(array_merge(
            ['numero', $textColumn],
            $this->extraColumns($tipo, $tribunalUpper),
        )));

        $query = DB::table($table)
            ->select($columns)
            ->where('numero', $numero);

        // STF guarda súmula comum e vinculante na mesma tabela (índice único
        // numero+is_vinculante); desambiguamos pelo flag para não misturar as duas.
        if ($tipo === 'sumula' && $tribunalUpper === 'STF') {
            $query->where('is_vinculante', 0);
        } elseif ($tipo === 'sumula-vinculante') {
            $query->where('is_vinculante', 1);
        }

        $row = $query->first();

        if (! $row) {
            return null;
        }

        $texto = trim((string) ($row->{$textColumn} ?? ''));

        if ($tipo === 'tese') {
            return [
                'tribunal' => $tribunalUpper,
                'tipo' => 'tese',
                'numero' => (int) $row->numero,
                'texto' => $texto,
                'tema' => $this->teseTema($tribunalUpper, $row),
                'tese' => $texto,
                'situacao' => $this->teseSituacao($tribunalUpper, $row),
                'url' => $this->canonicalUrl('tese', $tribunalUpper, $numero),
            ];
        }

        return [
            'tribunal' => $tribunalUpper,
            'tipo' => $tipo === 'sumula-vinculante' ? 'sumula-vinculante' : 'sumula',
            'numero' => (int) $row->numero,
            'texto' => $texto,
            'situacao' => $this->sumulaSituacao($tribunalUpper, $row),
            'url' => $this->sumulaUrl($tipo, $tribunalUpper, $numero),
        ];
    }

    /**
     * Colunas adicionais necessárias para montar os campos extras (tema/situação).
     *
     * @return array<int, string>
     */
    private function extraColumns(string $tipo, string $tribunalUpper): array
    {
        if ($tipo === 'tese') {
            return array_values(array_filter([
                self::TESE_TEMA_COLUMNS[$tribunalUpper] ?? null,
                self::TESE_SITUACAO_COLUMNS[$tribunalUpper] ?? null,
            ]));
        }

        // Súmulas: situação derivada do status de cancelamento, quando existir.
        if ($tribunalUpper === 'STF') {
            return ['obs'];
        }

        if (in_array($tribunalUpper, ['STJ', 'TNU'], true)) {
            return ['isCancelada'];
        }

        return [];
    }

    /**
     * Descrição/título do TEMA da tese (vazio se o tribunal não tiver a coluna).
     */
    private function teseTema(string $tribunalUpper, object $row): string
    {
        $column = self::TESE_TEMA_COLUMNS[$tribunalUpper] ?? null;

        if ($column === null) {
            return '';
        }

        $tema = trim((string) ($row->{$column} ?? ''));

        // STF prefixa o tema com "N - "; removemos para manter a grafia limpa
        // (mesma normalização do endpoint autenticado getTese).
        if ($tribunalUpper === 'STF') {
            $tema = (string) preg_replace('/^\d+\s*-\s*/', '', $tema);
        }

        return $tema;
    }

    /**
     * Situação/status da tese (vazio se o tribunal não expõe a coluna).
     */
    private function teseSituacao(string $tribunalUpper, object $row): string
    {
        $column = self::TESE_SITUACAO_COLUMNS[$tribunalUpper] ?? null;

        if ($column === null) {
            return '';
        }

        return trim((string) ($row->{$column} ?? ''));
    }

    /**
     * Situação da súmula. Grafia canônica "Cancelada" quando há sinal de
     * cancelamento/revogação; caso contrário, "".
     */
    private function sumulaSituacao(string $tribunalUpper, object $row): string
    {
        if ($tribunalUpper === 'STF') {
            $obs = mb_strtolower((string) ($row->obs ?? ''));

            return (str_contains($obs, 'revogada') || str_contains($obs, 'cancelada'))
                ? 'Cancelada'
                : '';
        }

        if (in_array($tribunalUpper, ['STJ', 'TNU'], true)) {
            return ((int) ($row->isCancelada ?? 0)) === 1 ? 'Cancelada' : '';
        }

        return '';
    }

    /**
     * Resolve o nome da tabela legada via registry (sem concatenação fixa).
     */
    private function tableFor(string $tipo, string $tribunalUpper): string
    {
        $config = $this->registry->get($tribunalUpper);
        $key = in_array($tipo, ['sumula', 'sumula-vinculante'], true) ? 'sumulas' : 'teses';
        $suffix = $config->tables()[$key][0];

        return $config->tribunalLower().'_'.$suffix;
    }

    /**
     * URL canônica da súmula: página individual quando existir; vinculante usa o
     * prefixo "sv" (padrão do site); senão, link para a busca.
     */
    private function sumulaUrl(string $tipo, string $tribunalUpper, int $numero): string
    {
        if ($tipo === 'sumula-vinculante') {
            return route('stfsumulapage', ['sumula' => 'sv'.$numero]);
        }

        return $this->canonicalUrl('sumula', $tribunalUpper, $numero);
    }

    /**
     * URL canônica: página individual quando existir; senão, link para a busca.
     */
    private function canonicalUrl(string $tipo, string $tribunalUpper, int $numero): string
    {
        if (in_array($tribunalUpper, self::INDIVIDUAL_PAGE_TRIBUNALS, true)) {
            return route(strtolower($tribunalUpper).$tipo.'page', [$tipo => $numero]);
        }

        return url('/?tribunal='.$tribunalUpper.'&q='.$numero);
    }
}
