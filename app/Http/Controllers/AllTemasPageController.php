<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class AllTemasPageController extends Controller
{
    public function index()
    {
        $display_pdf = '';
        $temas = DB::table('pesquisas')
                ->select('keyword', 'label', 'slug', 'concept', 'concept_validated_at')
                ->whereNull('checked_at')
                ->whereNotNull('created_at')
                ->whereNotNull('slug')
                ->orderBy(DB::raw("COALESCE(label, REPLACE(keyword, '\"', ''))")) //GET RID OFF QUOTES ONLY FOR ALPHABETICAL ORDER
                ->get();

        $description = 'Pesquisas prontas de Teses de Repercussão e Repetitivos e de Súmulas dos tribunais superiores (STF, STJ, TST) e de outros órgãos federais relevantes (TNU, FONAJE/CNJ, CEJ/CJF, TCU, CARF)';
        $perc_total_concepts = "";
        //from the total temas, calculate the percentage of validated concepts, if admin
        $admin = false;
        if (auth()->check()) {
            //check the email
            $useremail = auth()->user()->email;
            if(in_array($useremail, ['mauluis@gmail.com','trator70@gmail.com','ivanaredler@gmail.com'])) {
                $admin = true;
            }
        }

        if($admin) {
            $total_temas = count($temas);
            $total_concepts = 0;
            foreach ($temas as $tema) {
                if ($tema->concept_validated_at) {
                    $total_concepts++;
                }
            }
            $percentage_concepts = round($total_concepts/$total_temas*100, 2);
            $perc_total_concepts = "$total_concepts de $total_temas com resumo ($percentage_concepts%)"; 
        }
        return view('front.temas', compact('temas','display_pdf','description', 'perc_total_concepts'));

    } //end public function
}
