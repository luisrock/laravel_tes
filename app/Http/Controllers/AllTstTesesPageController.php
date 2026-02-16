<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AllTstTesesPageController extends Controller
{
    public function index()
    {
        $teses = DB::table('tst_teses')
            ->select('*')
            ->orderBy('numero', 'DESC')
            ->get();

        foreach ($teses as $tese) {
            $tese->isCancelada = false;
            $tese->tema_pure_text = isset($tese->tema) ? (string) $tese->tema : '';
            $tese->tese_texto = isset($tese->texto) ? (string) $tese->texto : '';
            $tese->tese_texto = trim((string) preg_replace('/\s*\|\s*Relator\(a\)?:?.*$/iu', '', $tese->tese_texto));
            $tese->tempo = '';
            $tese->link_externo = isset($tese->link) ? (string) $tese->link : '';
        }

        $tribunal = 'TST';
        $count = $teses->count();
        $display_pdf = false;

        $label = 'Teses Vinculantes do Tribunal Superior do Trabalho - TST';

        $dataAtual = now()->format('m/Y');
        $description = "Consulte {$count} teses vinculantes do TST. Pesquisa por número de tema ou assunto. Atualizado em {$dataAtual}.";

        $breadcrumb = [
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Índice', 'url' => url('/index')],
            ['name' => 'Teses TST', 'url' => null],
        ];

        $admin = false;
        if (auth()->check()) {
            $useremail = auth()->user()->email;
            if (in_array($useremail, ['mauluis@gmail.com', 'trator70@gmail.com', 'ivanaredler@gmail.com'])) {
                $admin = true;
            }
        }

        return view('front.teses_tst', compact('tribunal', 'teses', 'count', 'label', 'description', 'admin', 'display_pdf', 'breadcrumb'));
    }
}
