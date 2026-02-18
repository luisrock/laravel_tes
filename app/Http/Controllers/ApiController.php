<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

// Atenção: headers para o request devem conter:
// 'Content-Type: application/json'
// 'Accept: application/json'

class ApiController extends Controller
{
    public function index(Request $request)
    {
        // return $request;

        $lista_tribunais = config('tes_constants.lista_tribunais');
        $lista_tribunais_string = implode(',', array_keys($lista_tribunais));

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
        $tribunal_array = $lista_tribunais[$tribunal_upper];
        $tese_name = $tribunal_array['tese_name'];

        // search in db (not through tribunal API)
        if ($lista_tribunais[$tribunal]['db']) {
            $output = tes_search_db($keyword, $tribunal_lower, $tribunal_array);
        } else {
            // Getting the results by calling the tribunal API
            // tratando keyword
            $keyword = buildFinalSearchStringForApi($keyword, $tribunal_upper);
            $output = call_request_api($tribunal_lower, $keyword);
        }

        if (is_array($output)) {
            $final_result['total_sum'] = $output['sumula']['total'];
            $final_result['total_rep'] = $output[$tese_name]['total'];
            $final_result['hits_sum'] = $output['sumula']['hits'];
            $final_result['hits_rep'] = $output[$tese_name]['hits'];
        } elseif (is_string($output)) {
            $final_result['error'] = $output;
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

        $lista_tribunais = Config::get('tes_constants.lista_tribunais');
        $selectedThemes = [];
        $themesChecked = 0;

        foreach ($temas as $tema) {
            $keyword = $tema->keyword;
            $stfCount = 0;
            $stjCount = 0;

            // Check STF results
            if ($lista_tribunais['STF']['db']) {
                $stf_output = tes_search_db($keyword, 'stf', $lista_tribunais['STF']);
                $stfCount = $stf_output['sumula']['total'] + $stf_output['tese']['total'];
            }

            // Check STJ results
            if ($lista_tribunais['STJ']['db']) {
                $stj_output = tes_search_db($keyword, 'stj', $lista_tribunais['STJ']);
                $stjCount = $stj_output['sumula']['total'] + $stj_output['tese']['total'];
            }

            // Check if total STF + STJ judgments meet the minimum requirement
            if (($stfCount + $stjCount) >= $minJudgments) {
                $selectedThemes[] = $tema;

                if (count($selectedThemes) >= $limit) {
                    break;
                }
            }

            $themesChecked++;

            // Prevent infinite loop - if we've checked all themes and don't have enough
            if ($themesChecked >= count($temas)) {
                break;
            }
        }

        if (empty($selectedThemes)) {
            return response()->json([
                'success' => false,
                'error' => 'Nenhum tema encontrado com pelo menos '.$minJudgments.' julgados do STF ou STJ.',
            ], 404);
        }

        // Sort selected themes alphabetically by label (or keyword if label is empty)
        usort($selectedThemes, function ($a, $b) {
            $labelA = $a->label ?? $a->keyword;
            $labelB = $b->label ?? $b->keyword;

            return strcasecmp($labelA, $labelB);
        });

        // Build response with full theme data
        $response = [];
        foreach ($selectedThemes as $tema) {
            $keyword = $tema->keyword;
            $label = $tema->label ?? $keyword;

            // Get all tribunal results for this theme
            $tribunais = array_keys($lista_tribunais);
            $themeOutput = [];

            foreach ($tribunais as $tribunal) {
                if ($lista_tribunais[$tribunal]['db'] === false && $tribunal !== 'STF') {
                    continue;
                }

                $tribunal_lower = strtolower($tribunal);
                $tribunal_upper = strtoupper($tribunal);
                $tribunal_array = $lista_tribunais[$tribunal_upper];
                $output_tribunal = tes_search_db($keyword, $tribunal_lower, $tribunal_array);

                // Tratar trib_rep_tema para teses do STF
                if ($tribunal === 'STF' && isset($output_tribunal['tese']['hits'])) {
                    foreach ($output_tribunal['tese']['hits'] as &$hit) {
                        if (isset($hit['trib_rep_tema']) && ! empty($hit['trib_rep_tema'])) {
                            // Extrair número do início (antes do hífen)
                            if (preg_match('/^(\d+)\s*-\s*(.+)$/', $hit['trib_rep_tema'], $matches)) {
                                $hit['trib_rep_numero'] = $matches[1];
                                $hit['trib_rep_tema'] = trim($matches[2]);
                            }
                        }
                    }
                }

                $themeOutput[$tribunal_lower] = $output_tribunal;
            }

            $response[] = [
                'id' => $tema->id,
                'keyword' => $tema->keyword,
                'label' => $label,
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
        $lista_tribunais = Config::get('tes_constants.lista_tribunais');

        $result = [];
        $total_global = 0;

        foreach ($lista_tribunais as $tribunal_upper => $tribunal_array) {
            // TCU usa API externa — excluído para manter o endpoint rápido
            if (! $tribunal_array['db']) {
                continue;
            }

            $tribunal_lower = strtolower($tribunal_upper);
            $output = tes_search_db($keyword, $tribunal_lower, $tribunal_array);

            $sumulas = $output['sumula']['total'] ?? 0;
            $teses = $output[$tribunal_array['tese_name']]['total'] ?? 0;
            $total = $sumulas + $teses;

            $result[$tribunal_lower] = [
                'sumulas' => $sumulas,
                'teses' => $teses,
                'total' => $total,
            ];

            $total_global += $total;
        }

        $result['meta'] = [
            'keyword' => $keyword,
            'total_global' => $total_global,
        ];

        return response()->json($result);
    }
}
