<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class TemaPageController extends Controller
{
    public function index($slug = null)
    {

        //Back to the search page, if we do not have a tema
        if(!$slug) {
            return redirect()->route('searchpage');
        }

        $get_keyword = DB::table('pesquisas')->select('id', 'keyword', 'label', 'concept', 'concept_validated_at')->where('slug', '=', $slug)->get();

        if(empty($get_keyword[0]) || empty($get_keyword[0]->keyword)) {
            return redirect()->route('searchpage');
        }

        $id = $get_keyword[0]->id;

        $keyword = $get_keyword[0]->keyword;
        
        if(!empty($get_keyword[0]->label)) {
            $label = $get_keyword[0]->label;
        } else {
            $label = $keyword;
        }

        $concept = $get_keyword[0]->concept;
        $concept_validated_at = $get_keyword[0]->concept_validated_at;
        if(empty($concept)) {
            $concept = '';
        }
        if(empty($concept_validated_at)) {
            $concept_validated_at = '';
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
            $output_tribunal = tes_search_db($keyword,$tribunal_lower,$tribunal_array);
            $output[$tribunal_lower] = $output_tribunal;
        } //END foreach

        //dd($output);

        $description = $label . ' - Conheça as Teses de Repercussão e de Repetitivos e as Súmulas dos tribunais superiores (STF, STJ, TST) e de outros órgãos relevantes federais (TNU, FONAJE/CNJ, CEJ/CJF, TCU, CARF) sobre o tema ' . $label;
        
        $html = view('front.tema', compact('id', 'keyword', 'label', 'output', 'display_pdf', 'description', 'concept', 'concept_validated_at'));
        return $html;
        

        
    } //end public function
}

#TODO: criar colunas concept e conccpet_validated_at na tabela pesquisas, usando o tableplus, exatamente como feito na db local