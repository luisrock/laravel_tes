<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class AllStfTesesPageController extends Controller
{
    public function index()
    {
        $teses = DB::table('stf_teses')
            //select all fields
            ->select('*')
            //order by numero DESC
            ->orderBy('numero', 'DESC')
            //get all from the DB (no items limit)
            ->get();

        $tribunal = 'STF';
        $count = $teses->count();
        $display_pdf = false;

        $label = 'Teses Vinculantes do Supremo Tribunal Federal - STF';
        $tese_route = 'stftesepage';
        
        // Meta description dinâmica para melhor CTR
        $dataAtual = now()->format('m/Y');
        $description = "Consulte {$count} teses de repercussão geral do STF. Pesquisa por número de tema ou assunto. Atualizado em {$dataAtual}.";
        
        // Breadcrumb
        $breadcrumb = [
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Índice', 'url' => url('/index')],
            ['name' => 'Teses STF', 'url' => null]
        ];
        
        $admin = false;
        if (auth()->check()) {
            //check the email
            $useremail = auth()->user()->email;
            if (in_array($useremail, ['mauluis@gmail.com', 'trator70@gmail.com', 'ivanaredler@gmail.com'])) {
                $admin = true;
            }
        }

        foreach ($teses as $tese) {
            $tese->isCancelada = 0;
            if (isset($tese->aprovadaEm) && $tese->aprovadaEm) {
                $tese->tempo = "Aprovada em {$tese->aprovadaEm}";
            } else {
                $tese->tempo = "";
            }
            $tese->tema_pure_text = preg_replace('/^\d+ - /', '', $tese->tema_texto);
        }

        // dd($teses);
        return view('front.teses', compact('tribunal', 'teses', 'count', 'label', 'description', 'admin', 'display_pdf', 'tese_route', 'breadcrumb'));
    } //end public function
}