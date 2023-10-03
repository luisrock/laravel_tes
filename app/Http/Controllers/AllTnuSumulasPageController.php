<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class AllTnuSumulasPageController extends Controller
{
    public function index()
    {
        $sumulas = DB::table('tnu_sumulas')
            //select all fields
            ->select('*')
            //order by numero DESC
            ->orderBy('numero', 'DESC')
            //get all from the DB (no items limit)
            ->get();

        foreach ($sumulas as $sum) {
            $text = "{$sum->titulo}. {$sum->texto}";
            //trim text
            $text = trim($text);
            //remove double spaces inside
            $text = preg_replace('/\s+/', ' ', $text);
            $sum->to_be_copied = $text;

            if (isset($sum->dados)) {
                $sum->obs = $sum->dados;
            } else {
                $sum->obs = '';
            }
            $sum->tempo = ' ';
        }

        $count = $sumulas->count();
        $display_pdf = false;
        $tribunal = 'TNU';
        $label = 'Súmulas da Turma Nacional de Uniformização dos Juizados Especiais Federais - TNU';
        $sumula_route = 'tnusumulapage';
        $description = "Relação de $label, com os respectivos textos";
        $admin = false;
        if (auth()->check()) {
            //check the email
            $useremail = auth()->user()->email;
            if (in_array($useremail, ['mauluis@gmail.com', 'trator70@gmail.com', 'ivanaredler@gmail.com'])) {
                $admin = true;
            }
        }
        // dd($sumulas);
        return view('front.sumulas', compact('tribunal', 'sumulas', 'count', 'label', 'description', 'admin', 'display_pdf', 'sumula_route'));
    } //end public function
}