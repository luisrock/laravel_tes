<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AllStjTesesPageController extends Controller
{
    public function index()
    {
        $teses = DB::table('stj_teses')
            // select all fields
            ->select('*')
            // order by numero DESC
            ->orderBy('numero', 'DESC')
            // get all from the DB (no items limit)
            ->get();

        $tribunal = 'STJ';
        $count = $teses->count();
        $display_pdf = false;

        $label = 'Temas Repetitivos e Teses Vinculantes do Superior Tribunal de Justiça - STJ';
        $tese_route = 'stjtesepage';

        // Meta description dinâmica para melhor CTR
        $dataAtual = now()->format('m/Y');
        $description = "Consulte {$count} temas repetitivos do STJ com a tese firmada. Pesquisa por número ou assunto. Atualizado em {$dataAtual}.";

        // Breadcrumb
        $breadcrumb = [
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Índice', 'url' => url('/index')],
            ['name' => 'Teses STJ', 'url' => null],
        ];

        $admin = false;
        if (auth()->check()) {
            // check the email
            $useremail = auth()->user()->email;
            if (in_array($useremail, ['mauluis@gmail.com', 'trator70@gmail.com', 'ivanaredler@gmail.com'])) {
                $admin = true;
            }
        }

        foreach ($teses as $tese) {
            $tese->isCancelada = ! empty($tese->situacao) && Str::contains($tese->situacao, 'ancelad');
            $tese->tempo = '';
            if (isset($tese->atualizadaEm) && $tese->atualizadaEm) {
                $tese->tempo = "Última atualização: {$tese->atualizadaEm}";
            }
            $tese->tema_pure_text = '';
            if (isset($tese->tema) && $tese->tema) {
                $tese->tema_pure_text = $tese->tema;
            }
        }

        // dd($teses);
        return view('front.teses', compact('tribunal', 'teses', 'count', 'label', 'description', 'admin', 'display_pdf', 'tese_route', 'breadcrumb'));
    } // end public function
}
