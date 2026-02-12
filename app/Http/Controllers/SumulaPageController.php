<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class SumulaPageController extends Controller
{
    public function index()
    {
        $route = request()->route()->getName();

        $tribunal = "";
        $tribunal_nome_completo = '';
        $table = "";
        $allsumulasroute = "";
        if ($route == 'stfsumulapage') {
            $tribunal = 'STF';
            $tribunal_nome_completo = 'Supremo Tribunal Federal';
            $table = 'stf_sumulas';
            $allsumulasroute = 'stfallsumulaspage';
        } else if ($route == 'stjsumulapage') {
            $tribunal = 'STJ';
            $tribunal_nome_completo = 'Superior Tribunal de Justiça';
            $table = 'stj_sumulas';
            $allsumulasroute = 'stjallsumulaspage';
        } else if ($route == 'tstsumulapage') {
            $tribunal = 'TST';
            $tribunal_nome_completo = 'Tribunal Superior do Trabalho';
            $table = 'tst_sumulas';
            $allsumulasroute = 'tstallsumulaspage';
        } else if ($route == 'tnusumulapage') {
            $tribunal = 'TNU';
            $tribunal_nome_completo = 'Turma Nacional de Uniformização dos JEF';
            $table = 'tnu_sumulas';
            $allsumulasroute = 'tnuallsumulaspage';
        } else {
            return redirect()->route('searchpage');
        }

        //considering the route above, get the sumula id var using laravel method, Must be a number
        $sumula_id = intval(request()->route('sumula'));
        //if no sumula id, redirect to all sumulas page
        if (!$sumula_id) {
            return redirect()->route($allsumulasroute);
        }

        $sumula = DB::table($table)
            //select all fields
            ->select('*')
            ->where('id', $sumula_id)
            ->first();

        if (!$sumula) {
            return redirect()->route($allsumulasroute);
        }

        $text = "$tribunal, {$sumula->titulo}. {$sumula->texto}";
        $text = trim($text);
        //remove double spaces inside
        $text = preg_replace('/\s+/', ' ', $text);

        if ($tribunal == 'STF') {
            //add to_be_copied property
            $text = $text . " Aprovada em {$sumula->aprovadaEm}";
            $sumula->tempo = '';
            if (isset($sumula->aprovadaEm) && $sumula->aprovadaEm) {
                $sumula->tempo = "Aprovada em {$sumula->aprovadaEm}";
            }
            $sumula->isCancelada = 0;
            if (Str::contains(strtolower($sumula->obs), 'revogada') || Str::contains(strtolower($sumula->obs), 'cancelada')) {
                $sumula->isCancelada = 1;
            }
        } else if ($tribunal == 'STJ') {
            $text = $text . " Publicada em {$sumula->publicadaEm}";
            if (isset($sumula->ramos)) {
                $sumula->obs = $sumula->ramos;
            } else {
                $sumula->obs = '';
            }
            $sumula->tempo = ' ';
            if (isset($sumula->publicadaEm) && $sumula->publicadaEm) {
                $sumula->tempo = "Publicada em {$sumula->publicadaEm}";
            } else if (isset($sumula->julgadaEm) && $sumula->julgadaEm) {
                $sumula->tempo = "Julgada em {$sumula->julgadaEm}";
            }
            if (empty($sumula->link)) {
                $sumula->link = "https://scon.stj.jus.br/SCON/sumstj/";
            }
        } else if ($tribunal == 'TST') {
            if (isset($sumula->tema)) {
                $sumula->obs = $sumula->tema;
            } else {
                $sumula->obs = '';
            }
            $sumula->tempo = ' ';
        } else if ($tribunal == 'TNU') {
            if (isset($sumula->dados)) {
                $sumula->obs = $sumula->dados;
            } else {
                $sumula->obs = '';
            }
            $sumula->tempo = ' ';
        }

        //if there is no "." at the end of the text, add it
        if (!Str::endsWith($text, '.')) {
            $text = $text . '.';
        }
        $sumula->to_be_copied = $text;

        $display_pdf = false;
        $label = "{$sumula->titulo} do $tribunal_nome_completo - $tribunal";
        $description = "{$sumula->titulo} do $tribunal_nome_completo - $tribunal";

        // Breadcrumb
        $breadcrumb = [
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Índice', 'url' => url('/index')],
            ['name' => "Súmulas $tribunal", 'url' => route($allsumulasroute)],
            ['name' => $sumula->titulo, 'url' => null]
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
        return view('front.sumula', compact('tribunal', 'tribunal_nome_completo', 'sumula', 'label', 'description', 'admin', 'display_pdf', 'allsumulasroute', 'breadcrumb'));
    } //end public function
}