<?php

namespace App\Http\Controllers;

use App\Services\SearchDatabaseService;
use App\Services\SearchTribunalRegistry;
use App\Services\SearchTribunalResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Atenção: headers para o request devem conter:
// 'Content-Type: application/json'
// 'Accept: application/json'

class ApiController extends Controller
{
    public function __construct(
        private SearchDatabaseService $searchDatabaseService,
        private SearchTribunalRegistry $searchTribunalRegistry,
    ) {}

    public function index(Request $request)
    {
        // return $request;

        $lista_tribunais = $this->searchTribunalRegistry->allRaw();
        $lista_tribunais_string = implode(',', $this->searchTribunalRegistry->keys());

        // User is searching. Prepare and return results
        $query = $request->validate([
            // compat with extension
            'q' => 'required_without:keyword|min:3',
            'keyword' => 'required_without:q|min:3',
            'tribunal' => 'required|in:'.$lista_tribunais_string,
        ],
            [
                'q.required' => 'Por favor, defina o(s) termo(s) de busca.',
                'q.min' => 'O termo de busca deve conter ao menos três caracteres.',
                'tribunal.required' => 'Por favor, indique o tribunal/órgão para a sua pesquisa.',
                'tribunal.in' => 'Por favor, indique um tribunal/órgão válido para a sua pesquisa.',
            ]
        );

        $final_result = [];
        $keyword = $query['q'] ?? $query['keyword']; // compat with extension
        $tribunal = $query['tribunal'];
        $tribunal_lower = strtolower($tribunal);
        $tribunal_upper = strtoupper($tribunal);
        $tribunal_array = $this->searchTribunalRegistry->get($tribunal_upper);
        $tese_name = $tribunal_array->teseName();

        if ($tribunal_array->usesDatabase()) {
            $rawOutput = $this->searchDatabaseService->search($keyword, $tribunal_lower, $tribunal_array);
        } else {
            $normalizedKeyword = buildFinalSearchStringForApi($keyword, $tribunal_upper);
            $rawOutput = call_request_api($tribunal_lower, $normalizedKeyword);
        }

        if (is_array($rawOutput)) {
            $final_result = SearchTribunalResult::fromArray($tese_name, $rawOutput)->toPublicApiArray();
        } elseif (is_string($rawOutput)) {
            $final_result['error'] = $rawOutput;
        }

        return $final_result;

    } // end public function

    public function getSumula($tribunal, $numero)
    {
        // Validate tribunal
        $tribunal = strtoupper($tribunal);
        if (! in_array($tribunal, ['STF', 'STJ'])) {
            return response()->json([
                'success' => false,
                'error' => 'Tribunal não suportado. Use STF ou STJ.',
            ], 400);
        }

        // Validate numero
        if (! is_numeric($numero)) {
            return response()->json([
                'success' => false,
                'error' => 'Número deve ser um valor numérico.',
            ], 400);
        }

        // Map tribunal to table
        $table = strtolower($tribunal).'_sumulas';

        // Get sumula by numero
        $sumula = DB::table($table)
            ->select('*')
            ->where('numero', $numero)
            ->first();

        if (! $sumula) {
            return response()->json([
                'success' => false,
                'error' => 'Súmula não encontrada.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $sumula,
        ]);
    }

    public function getTese($tribunal, $numero)
    {
        // Validate tribunal
        $tribunal = strtoupper($tribunal);
        if (! in_array($tribunal, ['STF', 'STJ'])) {
            return response()->json([
                'success' => false,
                'error' => 'Tribunal não suportado. Use STF ou STJ.',
            ], 400);
        }

        // Validate numero
        if (! is_numeric($numero)) {
            return response()->json([
                'success' => false,
                'error' => 'Número deve ser um valor numérico.',
            ], 400);
        }

        // Map tribunal to table
        $table = strtolower($tribunal).'_teses';

        // Get tese by numero
        $tese = DB::table($table)
            ->select('*')
            ->where('numero', $numero)
            ->first();

        if (! $tese) {
            return response()->json([
                'success' => false,
                'error' => 'Tese não encontrada.',
            ], 404);
        }

        // Converter para array para manipulação
        $teseArray = (array) $tese;

        // Tratar tema_texto APENAS para STF
        if ($tribunal === 'STF') {
            // Verificar possíveis nomes do campo tema
            $camposTema = ['tema_texto', 'tema'];

            foreach ($camposTema as $campo) {
                if (isset($teseArray[$campo]) && ! empty($teseArray[$campo])) {
                    // Remove qualquer quantidade de dígitos + hífen do início
                    $teseArray[$campo] = preg_replace('/^\d+\s*-\s*/', '', $teseArray[$campo]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $teseArray,
        ]);
    }

    public function updateTese($tribunal, $numero, Request $request)
    {
        // Validate tribunal
        $tribunal = strtoupper($tribunal);
        if (! in_array($tribunal, ['STF', 'STJ'])) {
            return response()->json([
                'success' => false,
                'error' => 'Tribunal não suportado. Use STF ou STJ.',
            ], 400);
        }

        // Validate numero
        if (! is_numeric($numero)) {
            return response()->json([
                'success' => false,
                'error' => 'Número deve ser um valor numérico.',
            ], 400);
        }

        // Validate tese_texto in request
        // Aceita apenas null explícito (para limpar) ou string não-vazia (para atualizar)
        // String vazia retorna erro para alertar o usuário sobre possível erro acidental
        // Recomendação: use DELETE /api/tese/{tribunal}/{numero}/tese_texto para limpar explicitamente

        // Verificar se string vazia ANTES da validação - verifica diretamente no JSON decodificado
        $jsonContent = $request->getContent();
        if (! empty($jsonContent)) {
            $jsonData = json_decode($jsonContent, true);
            if (isset($jsonData['tese_texto']) && $jsonData['tese_texto'] === '') {
                return response()->json([
                    'success' => false,
                    'error' => 'O campo tese_texto não pode estar vazio. Use null para limpar ou o endpoint DELETE /api/tese/{tribunal}/{numero}/tese_texto para remover explicitamente.',
                ], 422);
            }
        }

        $validated = $request->validate([
            'tese_texto' => 'nullable|string|max:65535',
        ], [
            'tese_texto.string' => 'O campo tese_texto deve ser uma string.',
            'tese_texto.max' => 'O campo tese_texto excede o tamanho máximo permitido.',
        ]);

        // Verificar novamente se string vazia (após trim) - rejeitar para evitar erro acidental
        if (isset($validated['tese_texto']) && is_string($validated['tese_texto']) && trim($validated['tese_texto']) === '') {
            return response()->json([
                'success' => false,
                'error' => 'O campo tese_texto não pode estar vazio. Use null para limpar ou o endpoint DELETE /api/tese/{tribunal}/{numero}/tese_texto para remover explicitamente.',
            ], 422);
        }

        // Se null, converte para string vazia para limpar o campo
        // Se string não-vazia, usa o texto fornecido
        $tese_texto = $validated['tese_texto'] === null ? '' : $validated['tese_texto'];

        // Map tribunal to table
        $table = strtolower($tribunal).'_teses';

        // Check if tese exists
        $tese = DB::table($table)
            ->select('*')
            ->where('numero', $numero)
            ->first();

        if (! $tese) {
            return response()->json([
                'success' => false,
                'error' => 'Tese não encontrada.',
            ], 404);
        }

        // Update tese_texto
        DB::table($table)
            ->where('numero', $numero)
            ->update(['tese_texto' => $tese_texto]);

        // Get updated tese
        $teseUpdated = DB::table($table)
            ->select('*')
            ->where('numero', $numero)
            ->first();

        // Converter para array para manipulação
        $teseArray = (array) $teseUpdated;

        // Tratar tema_texto APENAS para STF (igual getTese)
        if ($tribunal === 'STF') {
            // Verificar possíveis nomes do campo tema
            $camposTema = ['tema_texto', 'tema'];

            foreach ($camposTema as $campo) {
                if (isset($teseArray[$campo]) && ! empty($teseArray[$campo])) {
                    // Remove qualquer quantidade de dígitos + hífen do início
                    $teseArray[$campo] = preg_replace('/^\d+\s*-\s*/', '', $teseArray[$campo]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Tese atualizada com sucesso.',
            'data' => $teseArray,
        ]);
    }

    public function deleteTeseTexto($tribunal, $numero)
    {
        // Validate tribunal
        $tribunal = strtoupper($tribunal);
        if (! in_array($tribunal, ['STF', 'STJ'])) {
            return response()->json([
                'success' => false,
                'error' => 'Tribunal não suportado. Use STF ou STJ.',
            ], 400);
        }

        // Validate numero
        if (! is_numeric($numero)) {
            return response()->json([
                'success' => false,
                'error' => 'Número deve ser um valor numérico.',
            ], 400);
        }

        // Map tribunal to table
        $table = strtolower($tribunal).'_teses';

        // Check if tese exists
        $tese = DB::table($table)
            ->select('*')
            ->where('numero', $numero)
            ->first();

        if (! $tese) {
            return response()->json([
                'success' => false,
                'error' => 'Tese não encontrada.',
            ], 404);
        }

        // Update tese_texto to empty string (limpa o campo)
        DB::table($table)
            ->where('numero', $numero)
            ->update(['tese_texto' => '']);

        // Get updated tese
        $teseUpdated = DB::table($table)
            ->select('*')
            ->where('numero', $numero)
            ->first();

        // Converter para array para manipulação
        $teseArray = (array) $teseUpdated;

        // Tratar tema_texto APENAS para STF (igual getTese)
        if ($tribunal === 'STF') {
            // Verificar possíveis nomes do campo tema
            $camposTema = ['tema_texto', 'tema'];

            foreach ($camposTema as $campo) {
                if (isset($teseArray[$campo]) && ! empty($teseArray[$campo])) {
                    // Remove qualquer quantidade de dígitos + hífen do início
                    $teseArray[$campo] = preg_replace('/^\d+\s*-\s*/', '', $teseArray[$campo]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Texto da tese removido com sucesso.',
            'data' => $teseArray,
        ]);
    }

    public function getRandomThemes($limit = null, $minJudgments = null)
    {
        // Set default values if parameters are not provided
        if ($limit === null) {
            $limit = 5; // default limit
        }
        if ($minJudgments === null) {
            $minJudgments = 2; // default min_judgments
        }

        // Validate parameters
        if (! is_numeric($limit) || $limit < 1 || $limit > 50) {
            return response()->json([
                'success' => false,
                'error' => 'O parâmetro limit deve ser um número entre 1 e 50.',
            ], 400);
        }

        if (! is_numeric($minJudgments) || $minJudgments < 1) {
            return response()->json([
                'success' => false,
                'error' => 'O parâmetro min_judgments deve ser um número maior que 0.',
            ], 400);
        }

        // Get all valid themes from pesquisas table WITHOUT ordering to allow random selection
        $temas = DB::table('pesquisas')
            ->select('id', 'keyword', 'label', 'slug', 'concept', 'concept_validated_at')
            ->whereNull('checked_at')
            ->whereNotNull('created_at')
            ->whereNotNull('slug')
            ->get()
            ->all(); // transforma em array para poder usar shuffle

        // Embaralha os temas para garantir aleatoriedade
        shuffle($temas);

        $selectedThemes = $this->selectThemes($temas, (int) $limit, (int) $minJudgments);

        if (empty($selectedThemes)) {
            return response()->json([
                'success' => false,
                'error' => 'Nenhum tema encontrado com pelo menos '.$minJudgments.' julgados do STF ou STJ.',
            ], 404);
        }

        // Build response with full theme data
        $response = [];
        foreach ($selectedThemes as $tema) {
            $allResults = $this->searchDatabaseService->searchAllDatabaseTribunals($tema->keyword);

            $themeOutput = [];
            foreach ($allResults as $tribunalLower => $tribunalResult) {
                $themeOutput[$tribunalLower] = $this->normalizeTribunalOutput(strtoupper($tribunalLower), $tribunalResult);
            }

            $response[] = [
                'id' => $tema->id,
                'keyword' => $tema->keyword,
                'label' => $tema->label ?? $tema->keyword,
                'slug' => $tema->slug,
                'concept' => $tema->concept,
                'concept_validated_at' => $tema->concept_validated_at,
                'url' => url('/tema/'.$tema->slug),
                'tribunais' => $themeOutput,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $response,
            'total_found' => count($response),
            'requested_limit' => $limit,
            'min_judgments_required' => $minJudgments,
        ]);
    }

    /**
     * Busca unificada: retorna contagens de súmulas e teses por tribunal.
     * Endpoint público (sem autenticação), ideal para a extensão Chrome.
     * TCU excluído por usar API externa (lento demais para uso síncrono).
     */
    public function unifiedSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'keyword' => 'required|string|min:3',
        ], [
            'keyword.required' => 'Por favor, defina o(s) termo(s) de busca.',
            'keyword.min' => 'O termo de busca deve conter ao menos três caracteres.',
        ]);

        $keyword = $request->input('keyword');

        $allResults = $this->searchDatabaseService->searchAllDatabaseTribunals($keyword);

        $result = [];
        $total_global = 0;

        foreach ($allResults as $tribunalLower => $output) {
            $result[$tribunalLower] = $output->toUnifiedSummaryArray();
            $total_global += $result[$tribunalLower]['total'];
        }

        $result['meta'] = ['keyword' => $keyword, 'total_global' => $total_global];

        return response()->json($result);
    }

    private function selectThemes(array $themes, int $limit, int $minJudgments): array
    {
        $selected = [];

        foreach ($themes as $theme) {
            if ($this->countJudgmentsFor('STF', $theme->keyword) + $this->countJudgmentsFor('STJ', $theme->keyword) >= $minJudgments) {
                $selected[] = $theme;

                if (count($selected) >= $limit) {
                    break;
                }
            }
        }

        usort($selected, function ($a, $b) {
            return strcasecmp($a->label ?? $a->keyword, $b->label ?? $b->keyword);
        });

        return $selected;
    }

    private function countJudgmentsFor(string $tribunalUpper, string $keyword): int
    {
        $config = $this->searchTribunalRegistry->get($tribunalUpper);

        if (! $config->usesDatabase()) {
            return 0;
        }

        return $this->searchDatabaseService
            ->searchResult($keyword, strtolower($tribunalUpper), $config)
            ->totalCount();
    }

    private function normalizeTribunalOutput(string $tribunalUpper, SearchTribunalResult $result): array
    {
        $output = $result->toArray();

        if ($tribunalUpper !== 'STF' || ! isset($output['tese']['hits']) || ! is_array($output['tese']['hits'])) {
            return $output;
        }

        foreach ($output['tese']['hits'] as &$hit) {
            if (! is_array($hit) || empty($hit['trib_rep_tema']) || ! is_string($hit['trib_rep_tema'])) {
                continue;
            }

            if (preg_match('/^(\d+)\s*-\s*(.+)$/', $hit['trib_rep_tema'], $matches)) {
                $hit['trib_rep_numero'] = $matches[1];
                $hit['trib_rep_tema'] = trim($matches[2]);
            }
        }

        return $output;
    }
}
