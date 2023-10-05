<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class TesePageController extends Controller
{
    public function index()
    {
        $route = request()->route()->getName();

        $tribunal = "";
        $tribunal_nome_completo = '';
        $table = "";
        $alltesesroute = "";
        if ($route == 'stftesepage') {
            $tribunal = 'STF';
            $tribunal_nome_completo = 'Supremo Tribunal Federal';
            $table = 'stf_teses';
            $alltesesroute = 'stfalltesespage';
        } else if ($route == 'stjtesepage') {
            $tribunal = 'STJ';
            $tribunal_nome_completo = 'Superior Tribunal de Justiça';
            $table = 'stj_teses';
            $alltesesroute = 'stjalltesespage';
        } else {
            return redirect()->route('searchpage');
        }

        $tese_id = intval(request()->route('tese'));
        //if no tese id, redirect to all teses page
        if (!$tese_id) {
            return redirect()->route($alltesesroute);
        }

        $tese = DB::table($table)
            //select all fields
            ->select('*')
            ->where('id', $tese_id)
            ->first();

        if (!$tese) {
            return redirect()->route($alltesesroute);
        }

        $tese_isCancelada = 0;
        $have_tese = !empty($tese->tese_texto);

        // dd($tese);
        if ($tribunal == 'STF') {

            if (!Str::endsWith($tese->tema_texto, '.')) {
                $tese->tema_texto = $tese->tema_texto . '.';
            }

            if (!Str::endsWith($tese->tese_texto, '.')) {
                $tese->tese_texto = $tese->tese_texto . '.';
            }
        } else if ($tribunal == 'STJ') {
            $tese->tema_texto = $tese->numero . " - " . $tese->tema;
        }

        $text = "$tribunal, Tema {$tese->tema_texto} TESE: {$tese->tese_texto}";
        $text = trim($text);
        //remove double spaces inside
        $text = preg_replace('/\s+/', ' ', $text);

        if ($tribunal == 'STF') {
            //add to_be_copied property
            $text .= " " . $tese->relator . ', ' . $tese->acordao . '. ';
            $tese->tempo = '';
            if (isset($tese->aprovadaEm) && $tese->aprovadaEm) {
                $tese->tempo = "Aprovada em {$tese->aprovadaEm}";
            }
            if ($tese->tempo) {
                $text .= $tese->tempo;
            }
            $tese->titulo = "TEMA {$tese->numero}";
            $tese->questao = "QUESTÃO: " . preg_replace('/^\d+ - /', '', $tese->tema_texto);
            $tese->texto = $tese->tese_texto;
            $tese->text_muted = "{$tese->tempo}.";
        } else if ($tribunal == 'STJ') {

            //add to_be_copied property
            $text .= " " . $tese->orgao . ', situação: ' . $tese->situacao . '. ';
            $tese->tempo = '';
            if (isset($tese->atualizadaEm) && $tese->atualizadaEm) {
                $tese->tempo = "Última atualização: {$tese->atualizadaEm}";
            }
            if ($tese->tempo) {
                $text .= $tese->tempo;
            }
            $tese->titulo = "TEMA {$tese->numero}";
            $tese->questao = $tese->tema;
            $tese->texto = $tese->tese_texto;
            $tese->text_muted = "{$tese->orgao}. Situação: {$tese->situacao} (última atualização em {$tese->atualizadaEm}).";

            if ($tese->situacao == 'Cancelado' || $tese->situacao == 'Cancelada') {
                $tese_isCancelada = 1;
            }
            if (empty($tese->link)) {
                $tese->link = "https://processo.stj.jus.br/repetitivos/temas_repetitivos/pesquisa.jsp?novaConsulta=true&tipo_pesquisa=T&cod_tema_inicial={$tese->numero}&cod_tema_final={$tese->numero}";
            }
        }

        //if there is no "." at the end of the text, add it
        if (!Str::endsWith($text, '.')) {
            $text = $text . '.';
        }

        if ($have_tese) {
            $tese->to_be_copied = $text;
        } else {
            $tese->to_be_copied = ' ';
        }


        $display_pdf = false;
        $label = "TEMA {$tese->numero} do $tribunal_nome_completo - $tribunal";
        $description = $label;

        $admin = false;
        if (auth()->check()) {
            //check the email
            $useremail = auth()->user()->email;
            if (in_array($useremail, ['mauluis@gmail.com', 'trator70@gmail.com', 'ivanaredler@gmail.com'])) {
                $admin = true;
            }
        }
        // dd($teses);
        return view('front.tese', compact('tribunal', 'tribunal_nome_completo', 'tese', 'label', 'description', 'admin', 'display_pdf', 'alltesesroute'));
    } //end public function
}