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
        return view('front.temas', compact('temas','display_pdf','description'));
        
    } //end public function
}
