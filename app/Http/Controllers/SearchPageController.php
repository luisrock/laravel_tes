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
        $tribunal_array = $lista_tribunais[$tribunal_upper];
        $tese_name = $tribunal_array['tese_name'];
        $results_view = 'front.results.' . $tribunal_lower;
        

        //search in db (not through tribunal API)
        if($lista_tribunais[$tribunal]['db']) {

            $output = tes_search_db($keyword,$tribunal_lower,$tribunal_array);    
        
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

