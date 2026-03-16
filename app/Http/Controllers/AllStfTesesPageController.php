<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AllStfTesesPageController extends Controller
{
    public function index()
    {
        $teses = DB::table('stf_teses')
            ->select('*')
            ->orderBy('numero', 'DESC')
            ->get();

        $tribunal = 'STF';
        $count = $teses->count();
        $display_pdf = false;

        $label = 'Teses Vinculantes do Supremo Tribunal Federal - STF';
        $tese_route = 'stftesepage';

        $dataAtual = now()->format('m/Y');
        $description = "Consulte {$count} teses de repercussão geral do STF. Pesquisa por número de tema ou assunto. Atualizado em {$dataAtual}.";

        $breadcrumb = [
            ['name' => 'Início', 'url' => url('/')],
            ['name' => 'Índice', 'url' => url('/index')],
            ['name' => 'Teses STF', 'url' => null],
        ];

        $admin = auth()->check() && auth()->user()->hasRole('admin');

        // Admin busca ao vivo (bypass do cache) para ver badges atualizados imediatamente.
        // Usuários comuns usam cache de 1h.
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
            $tese->isCancelada = 0;
            if (isset($tese->aprovadaEm) && $tese->aprovadaEm) {
                $tese->tempo = "Aprovada em {$tese->aprovadaEm}";
            } else {
                $tese->tempo = '';
            }
            $tese->tema_pure_text = preg_replace('/^\d+ - /', '', $tese->tema_texto);
        }

        return view('front.teses', compact('tribunal', 'teses', 'count', 'label', 'description', 'admin', 'display_pdf', 'tese_route', 'breadcrumb', 'teses_with_ai', 'pending_job_ids', 'teses_with_acordaos_ids'));
    }
}
