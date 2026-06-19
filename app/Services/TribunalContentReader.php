<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Leitura pública e enxuta do teor de súmulas/teses das tabelas legadas dos
 * tribunais. Centraliza a resolução de tabela/coluna e a URL canônica para que
 * o endpoint público (S6) e futuras ampliações (S7) reusem a mesma lógica.
 *
 * Cobertura (S7): súmulas em STF/STJ/TST/TNU/CARF/CEJ e teses em STF/STJ/TST/TNU.
 * FONAJE fica adiado de propósito (3 sub-bases civ/cri/faz com numerações
 * próprias tornam `/FONAJE/{numero}` ambíguo) e TCU não usa banco (API externa).
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
     * @return array{tribunal: string, tipo: string, numero: int, texto: string, url: string}|null
     */
    public function find(string $tipo, string $tribunalUpper, int $numero): ?array
    {
        $tribunalUpper = strtoupper($tribunalUpper);

        if (! $this->supports($tipo, $tribunalUpper)) {
            return null;
        }

        $textColumn = self::TEXT_COLUMNS[$tipo][$tribunalUpper];
        $table = $this->tableFor($tipo, $tribunalUpper);

        $row = DB::table($table)
            ->select(['numero', $textColumn])
            ->where('numero', $numero)
            ->first();

        if (! $row) {
            return null;
        }

        return [
            'tribunal' => $tribunalUpper,
            'tipo' => $tipo,
            'numero' => (int) $row->numero,
            'texto' => trim((string) ($row->{$textColumn} ?? '')),
            'url' => $this->canonicalUrl($tipo, $tribunalUpper, $numero),
        ];
    }

    /**
     * Resolve o nome da tabela legada via registry (sem concatenação fixa).
     */
    private function tableFor(string $tipo, string $tribunalUpper): string
    {
        $config = $this->registry->get($tribunalUpper);
        $suffix = $config->tables()[$tipo === 'sumula' ? 'sumulas' : 'teses'][0];

        return $config->tribunalLower().'_'.$suffix;
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
