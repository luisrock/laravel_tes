<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

// TODO: Logar atualizações em outros tribunais além de STJ e STJ
// Lembrar que STF não está logando súmulas, pq teria que haver mudança drástica no código, já que
// da 1 a 58, súmulas terão o mesmo número (normal e vinculante)
// Não mexi pq o STF não edita súmula desde 2020, então não vale o esforço, terá que ser feito manualmente
class AtualizacoesPageController extends Controller
{
    public function get_original_by_item_id($tribunal, $item_id, $tipo)
    {
        $table = "{$tribunal}_{$tipo}s";
        $original = DB::table($table)
            ->select('*')
            ->where('id', $item_id)
            ->first();

        return $original;
    }

    public function index()
    {
        $unwanted_cols = ['ramos', 'situacao']; // Tenho dúvidas se não vamos perder algo... Checar depois

        $display_pdf = '';
        $logs = DB::table('update_log')
            ->select('*')
            // where update_at is less or equal than  7 days ago
            ->where('updated_at', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 7 DAY)'))
            ->orderBy('id', 'desc')
            ->get();

        $tribunais = [];
        foreach ($logs as $log) {
            $col_altered = $log->col_altered;
            $tribunal = $log->tribunal;
            $updated_at = $log->updated_at;
            $created_at = $log->created_at;
            $log->link = url("/{$log->tipo}/{$tribunal}/{$log->item_id}");
            if (! array_key_exists($tribunal, $tribunais)) {
                $tribunais[$tribunal] = [];
            }
            if (! array_key_exists('updates', $tribunais[$tribunal])) {
                $tribunais[$tribunal]['updates'] = [];
            }
            if (! array_key_exists('news', $tribunais[$tribunal])) {
                $tribunais[$tribunal]['news'] = [];
            }
            if ($created_at) {
                // if (count($tribunais[$tribunal]['news']) < 3000) {
                // get the original with $log->tema_id
                $original = $this->get_original_by_item_id($tribunal, $log->item_id, $log->tipo);
                if (empty($original)) {
                    continue;
                }
                if (empty($original->texto)) {
                    $original->texto = $original->tese_texto ?? '';
                }
                if (empty($original->texto)) {
                    $original->texto = $original->tese ?? '';
                }
                if (empty($original->texto)) {
                    $original->texto = $original->tema_texto ?? '';
                }
                if (empty($original->texto)) {
                    $original->texto = $original->tema ?? '';
                }
                $log->original = $original;
                $tribunais[$tribunal]['news'][] = $log;
                // }
            } else {
                if (in_array($col_altered, $unwanted_cols)) {
                    continue;
                }
                if (trim($log->old_value) === trim($log->new_value)) {
                    continue;
                }
                // if (count($tribunais[$tribunal]['updates']) < 3000) {
                $tribunais[$tribunal]['updates'][] = $log;
                // }
            }
        }

        // sort tribunais by tribunal, beginning with STF, then STJ, then the others
        $tribunais = collect($tribunais)->sortBy(function ($value, $key) {
            if ($key === 'stf') {
                return 1;
            }
            if ($key === 'stj') {
                return 2;
            }

            return 3;
        })->toArray();

        // dd($tribunais);

        $description = 'Recentes atualizações de Teses de Repercussão e Repetitivos e de Súmulas dos tribunais superiores (STF, STJ, TST) e de outros órgãos federais relevantes (TNU, FONAJE/CNJ, CEJ/CJF, TCU, CARF)';

        return view('front.atualizacoes', compact('tribunais', 'display_pdf', 'description'));
    } // end public function
}
