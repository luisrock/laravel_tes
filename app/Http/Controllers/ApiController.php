<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

//Atenção: headers para o request devem conter:
// 'Content-Type: application/json'
// 'Accept: application/json'

class ApiController extends Controller
{
    public function index(Request $request)
    {
        // return $request;

        $lista_tribunais = config('tes_constants.lista_tribunais');
        $lista_tribunais_string = implode(",",array_keys($lista_tribunais));

        //User is searching. Prepare and return results
        $query = $request->validate([
                //compat with extension
                'q' => 'required_without:keyword|min:3',
                'keyword' => 'required_without:q|min:3',
                'tribunal' => 'required|in:' . $lista_tribunais_string,
            ],
            [
                'q.required' => 'Por favor, defina o(s) termo(s) de busca.',
                'q.min' => 'O termo de busca deve conter ao menos três caracteres.',
                'tribunal.required' => 'Por favor, indique o tribunal/órgão para a sua pesquisa.',
                'tribunal.in' => 'Por favor, indique um tribunal/órgão válido para a sua pesquisa.',
            ]
        );

        $final_result = [];
        $keyword = $query['q'] ?? $query['keyword']; //compat with extension
        $tribunal = $query['tribunal'];
        $tribunal_lower = strtolower($tribunal);
        $tribunal_upper = strtoupper($tribunal);
        $tribunal_array = $lista_tribunais[$tribunal_upper];
        $tese_name = $tribunal_array['tese_name'];
        
        //search in db (not through tribunal API)
        if($lista_tribunais[$tribunal]['db']) {
            $output = tes_search_db($keyword,$tribunal_lower,$tribunal_array);
        } else {
        //Getting the results by calling the tribunal API
            //tratando keyword
            $keyword = buildFinalSearchStringForApi($keyword, $tribunal_upper);
            $output = call_request_api($tribunal_lower,$keyword);
        }

        if(is_array($output)) {
            $final_result['total_sum'] = $output['sumula']['total'];
            $final_result['total_rep'] = $output[$tese_name]['total'];
            $final_result['hits_sum'] = $output['sumula']['hits'];
            $final_result['hits_rep'] = $output[$tese_name]['hits'];
        } else if(is_string($output)) {
            $final_result['error'] = $output;
        }

        return $final_result; 
        
    } //end public function

    public function getSumula($tribunal, $numero)
    {
        // Validate tribunal
        $tribunal = strtoupper($tribunal);
        if (!in_array($tribunal, ['STF', 'STJ'])) {
            return response()->json([
                'success' => false,
                'error' => 'Tribunal não suportado. Use STF ou STJ.'
            ], 400);
        }

        // Validate numero
        if (!is_numeric($numero)) {
            return response()->json([
                'success' => false,
                'error' => 'Número deve ser um valor numérico.'
            ], 400);
        }

        // Map tribunal to table
        $table = strtolower($tribunal) . '_sumulas';

        // Get sumula by numero
        $sumula = DB::table($table)
            ->select('*')
            ->where('numero', $numero)
            ->first();

        if (!$sumula) {
            return response()->json([
                'success' => false,
                'error' => 'Súmula não encontrada.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $sumula
        ]);
    }

    public function getTese($tribunal, $numero)
    {
        // Validate tribunal
        $tribunal = strtoupper($tribunal);
        if (!in_array($tribunal, ['STF', 'STJ'])) {
            return response()->json([
                'success' => false,
                'error' => 'Tribunal não suportado. Use STF ou STJ.'
            ], 400);
        }

        // Validate numero
        if (!is_numeric($numero)) {
            return response()->json([
                'success' => false,
                'error' => 'Número deve ser um valor numérico.'
            ], 400);
        }

        // Map tribunal to table
        $table = strtolower($tribunal) . '_teses';

        // Get tese by numero
        $tese = DB::table($table)
            ->select('*')
            ->where('numero', $numero)
            ->first();

        if (!$tese) {
            return response()->json([
                'success' => false,
                'error' => 'Tese não encontrada.'
            ], 404);
        }

        // Converter para array para manipulação
        $teseArray = (array) $tese;
        
        // Tratar tema_texto APENAS para STF
        if ($tribunal === 'STF') {
            // Verificar possíveis nomes do campo tema
            $camposTema = ['tema_texto', 'tema'];
            
            foreach ($camposTema as $campo) {
                if (isset($teseArray[$campo]) && !empty($teseArray[$campo])) {
                    // Remove qualquer quantidade de dígitos + hífen do início
                    $teseArray[$campo] = preg_replace('/^\d+\s*-\s*/', '', $teseArray[$campo]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $teseArray
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
        if (!is_numeric($limit) || $limit < 1 || $limit > 50) {
            return response()->json([
                'success' => false,
                'error' => 'O parâmetro limit deve ser um número entre 1 e 50.'
            ], 400);
        }

        if (!is_numeric($minJudgments) || $minJudgments < 1) {
            return response()->json([
                'success' => false,
                'error' => 'O parâmetro min_judgments deve ser um número maior que 0.'
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
                'error' => 'Nenhum tema encontrado com pelo menos ' . $minJudgments . ' julgados do STF ou STJ.'
            ], 404);
        }

        // Sort selected themes alphabetically by label (or keyword if label is empty)
        usort($selectedThemes, function($a, $b) {
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
                        if (isset($hit['trib_rep_tema']) && !empty($hit['trib_rep_tema'])) {
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
                'url' => url('/tema/' . $tema->slug),
                'tribunais' => $themeOutput
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $response,
            'total_found' => count($response),
            'requested_limit' => $limit,
            'min_judgments_required' => $minJudgments
        ]);
    }
}
