<?php
//ALTER: rename fonaje tables
//ALTER: TST => merged $output['orientacao_jurisprudencia'] and $output['precedente_normativo'] to $output['orientacao_precedente']
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchPageController extends Controller
{

    public function index(Request $request)
    {

        $lista_tribunais = Config::get('tes_constants.lista_tribunais');
        $lista_tribunais_string = implode(",",array_keys($lista_tribunais));
        
        //Initial view (no search)
        if(empty($request->query())) {
            return view('front.search', compact('lista_tribunais'));
        }


        //User is searching. Prepare and return results
        $query = $request->validate([
                'q' => 'required|min:3',
                'tribunal' => 'required|in:' . $lista_tribunais_string,
            ],
            [
                'q.required' => 'Por favor, defina o(s) termo(s) de busca.',
                'q.min' => 'O termo de busca deve conter ao menos três caracteres.',
                'tribunal.required' => 'Por favor, indique o tribunal/órgão para a sua pesquisa.',
                'tribunal.in' => 'Por favor, indique um tribunal/órgão válido para a sua pesquisa.',
            ]
        );
        
        $keyword = $query['q'];
        $tribunal = $query['tribunal'];
        $tribunal_lower = strtolower($tribunal);
        $tribunal_upper = strtoupper($tribunal);
        $tese_name = $lista_tribunais[$tribunal_upper]['tese_name'];
        $results_view = 'front.results.' . $tribunal_lower;
        
        //preparing final array
        $output = [];
        $output['sumula'] = [];
        $output['sumula']['total'] = 0;
        $output['sumula']['hits'] = [];
        $output[$tese_name] = [];
        $output[$tese_name]['total'] = 0;
        $output[$tese_name]['hits'] = [];

        //search in db (not through tribunal API)
        if($lista_tribunais[$tribunal]['db']) {

            //preparing keyword for the full text search
            $arr = insertOperator(keyword_to_array($keyword));
            $final_str = buildFinalSearchString($arr);

            //getting the tables for the chosen tribunal
            $tables = $lista_tribunais[$tribunal_upper]['tables'];
            
            foreach ($tables as $table => $tab) {
                if(empty($tab)) {
                    continue;
                }
                $key = '';
                $it = '';
                if($table === 'sumulas') {
                    $key = 'sumula'; 
                    $it = 'sum';
                } else if($table === 'teses') {
                    $key = $tese_name;
                    $it = 'rep';
                }

                foreach ($tab as $t) {

                    $table_name = $tribunal_lower . '_' . $t;
                    $to_match = $lista_tribunais[$tribunal_upper]["to_match_$it"]; //para TNU QO, usar $to_match_sum
                    $query = "MATCH ($to_match) AGAINST (? IN BOOLEAN MODE)";                
                    $results = DB::table($table_name)
                            ->whereRaw($query, [$final_str])
                            ->orderBy('numero','desc')
                            ->get();
                    
                    //Laravel returns a stdClass. Converting to array
                    $results = json_decode(json_encode($results), true);
    
                    if($results) {                
                        $array_sum = call_adjust_query_function($tribunal_lower,$it,$results);
                        $output[$key]['hits'] = array_merge($output[$key]['hits'],$array_sum);
                    }
                    $output[$key]['total'] = count($output[$key]['hits']);
                } //end inner foreach
            } //end outter foreach

            //Para API, basta converter o $output em json e retorná-lo
            //dd($output);            
        
        //end db true
        } else {
        //Getting the results by calling the tribunal API
            $output = [];
            //tratando keyword
            $keyword = buildFinalSearchStringForApi($keyword, $tribunal_upper);
            
            $output = call_request_api($tribunal_lower,$keyword);
              
        }

        // dd($output);

        return view($results_view, compact('lista_tribunais','keyword', 'tribunal', 'output'));
        
    } //end public function
}

