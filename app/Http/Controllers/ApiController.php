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

        $final_result = [];
        $keyword = $query['q'];
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

        $final_result['total_sum'] = $output['sumula']['total'];
        $final_result['total_rep'] = $output[$tese_name]['total'];
        $final_result['hits_sum'] = $output['sumula']['hits'];
        $final_result['hits_rep'] = $output[$tese_name]['hits'];
        
        return $final_result; 
        
    } //end public function
}
