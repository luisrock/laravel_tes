<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class AllStfSumulasPageController extends Controller
{
    public function index()
    {
        $sumulas = DB::table('stf_sumulas')
            //select all fields
            ->select('*')
            //order by numero DESC
            ->orderBy('numero', 'DESC')
            //get all from the DB (no items limit)
            ->get();

        //retorder sumulas, so the is_vinculante ones come first
        $sumulas = $sumulas->sortByDesc('is_vinculante');

        //foreach sumula, add to_be_copied property with {{ $sum->titulo }}. {{ $sum->texto }} Aprovada em {{ $sum->aprovadaEm }}.
        foreach ($sumulas as $sum) {
            $text = "{$sum->titulo}. {$sum->texto} Aprovada em {$sum->aprovadaEm}";
            //trim text
            $text = trim($text);
            //remove double spaces inside
            $text = preg_replace('/\s+/', ' ', $text);
            $sum->to_be_copied = $text;
            $sum->tempo = '';
            if (isset($sum->aprovadaEm) && $sum->aprovadaEm) {
                $sum->tempo = "Aprovada em {$sum->aprovadaEm}";
            }
            $sum->isCancelada = 0;
            if (Str::contains(strtolower($sum->obs), 'revogada') || Str::contains(strtolower($sum->obs), 'cancelada')) {
                $sum->isCancelada = 1;
            }
        }


        $count = $sumulas->count();
        $display_pdf = false;
        $tribunal = 'STF';
        $label = 'Súmulas do Supremo Tribunal Federal - STF';
        $sumula_route = 'stfsumulapage';
        $description = "Relação de $label, com os respectivos textos";
        
        // Breadcrumb
        $breadcrumb = [
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Índice', 'url' => url('/index')],
            ['name' => 'Súmulas STF', 'url' => null]
        ];
        
        $admin = false;
        if (auth()->check()) {
            //check the email
            $useremail = auth()->user()->email;
            if (in_array($useremail, ['mauluis@gmail.com', 'trator70@gmail.com', 'ivanaredler@gmail.com'])) {
                $admin = true;
            }
        }
        // dd($sumulas);
        return view('front.sumulas', compact('tribunal', 'sumulas', 'count', 'label', 'description', 'admin', 'display_pdf', 'sumula_route', 'breadcrumb'));
    } //end public function
}