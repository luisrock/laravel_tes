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

        $admin = auth()->check() && auth()->user()->hasRole('admin');

        if ($admin) {
            $teses_with_ai = DB::table('tese_analysis_sections')
                ->where('tribunal', $tribunal)
                ->pluck('tese_id')
                ->unique()
                ->map(fn ($id) => (int) $id)
                ->values()
                ->toArray();

            $pending_job_ids = DB::table('tese_analysis_jobs')
                ->where('tribunal', $tribunal)
                ->whereIn('status', ['queued', 'running'])
                ->pluck('tese_id')
                ->unique()
                ->map(fn ($id) => (int) $id)
                ->values()
                ->toArray();

            $teses_with_acordaos_ids = DB::table('tese_acordaos')
                ->where('tribunal', $tribunal)
                ->whereNotNull('s3_key')
                ->whereNull('deleted_at')
                ->pluck('tese_id')
                ->unique()
                ->map(fn ($id) => (int) $id)
                ->values()
                ->toArray();
        } else {
            $teses_with_ai = get_teses_with_ai($tribunal);
            $pending_job_ids = [];
            $teses_with_acordaos_ids = [];
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

        return view('front.teses', compact('tribunal', 'teses', 'count', 'label', 'description', 'admin', 'display_pdf', 'tese_route', 'breadcrumb', 'teses_with_ai', 'pending_job_ids', 'teses_with_acordaos_ids'));
    } // end public function
}
