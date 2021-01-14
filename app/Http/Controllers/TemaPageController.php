<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class TemaPageController extends Controller
{
    public function index($tema = null)
    {

        //Back to the search page, if we do not have a tema
        if(!$tema) {
            return redirect()->route('searchpage');
        }

        $lista_tribunais = Config::get('tes_constants.lista_tribunais');
        $tribunais = array_keys($lista_tribunais);
        $display_pdf = '';

        //dd($tribunais);
        
        $output = [];

        //Getting the results by querying tes db for all tribunais (except the ones with API, excluding STF)
        //TODO: db for all tribunais
        foreach($tribunais as $tribunal) {
            if($lista_tribunais[$tribunal]['db'] === false && $tribunal !== 'STF' ) { 
                continue;
            }
            
            $output_tribunal = [];

            $tribunal_lower = strtolower($tribunal);
            $tribunal_upper = strtoupper($tribunal);
            $tribunal_array = $lista_tribunais[$tribunal_upper];
            $output_tribunal = tes_search_db($tema,$tribunal_lower,$tribunal_array);
            $output[$tribunal_lower] = $output_tribunal;
        } //END foreach

        //dd($output);

        $description = $tema . ' - Conheça as Teses de Repercussão/Repetitivos e Súmulas dos tribunais superiores (STF, STJ, TST) e de outros órgãos relevantes federais (TNU, FONAJE/CNJ, CEJ/CJF, TCU, CARF) sobre o tema ' . $tema;
        
        $html = view('front.tema', compact('tema', 'output', 'display_pdf', 'description'));
        return $html;
        

        
    } //end public function
}
