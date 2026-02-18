<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AllTnuTesesPageController extends Controller
{
    public function index()
    {
        $teses = DB::table('tnu_teses')
            ->select('*')
            ->orderBy('numero', 'DESC')
            ->get();

        foreach ($teses as $tese) {
            $tese->isCancelada = ! empty($tese->situacao) && Str::contains($tese->situacao, 'ancelad');
            $tese->tese_texto = isset($tese->tese) ? (string) $tese->tese : '';
            $tese->tema_pure_text = isset($tese->tema) ? (string) $tese->tema : '';
            $tese->tempo = '';
            if (! empty($tese->julgadoEm)) {
                $tese->tempo = "Julgado em {$tese->julgadoEm}";
            }
        }

        $tribunal = 'TNU';
        $count = $teses->count();
        $display_pdf = false;

        $label = 'Temas Representativos de Controvérsia da Turma Nacional de Uniformização - TNU';
        $tese_route = 'tnutesepage';

        $dataAtual = now()->format('m/Y');
        $description = "Consulte {$count} temas representativos da TNU com a tese firmada. Pesquisa por número ou assunto. Atualizado em {$dataAtual}.";

        $breadcrumb = [
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Índice', 'url' => url('/index')],
            ['name' => 'Teses TNU', 'url' => null],
        ];

        $admin = false;
        if (auth()->check()) {
            $useremail = auth()->user()->email;
            if (in_array($useremail, ['mauluis@gmail.com', 'trator70@gmail.com', 'ivanaredler@gmail.com'])) {
                $admin = true;
            }
        }

        return view('front.teses_tnu', compact('tribunal', 'teses', 'count', 'label', 'description', 'admin', 'display_pdf', 'tese_route', 'breadcrumb'));
    }
}
