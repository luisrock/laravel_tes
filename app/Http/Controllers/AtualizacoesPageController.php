<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


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
        $display_pdf = '';
        $logs = DB::table('update_log')
            ->select('*')
            //get last 20 lines
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        $tribunais = [];
        foreach ($logs as $log) {
            $tribunal = $log->tribunal;
            $updated_at = $log->updated_at;
            $created_at = $log->created_at;
            $log->link = url("/{$log->tipo}/{$tribunal}/{$log->item_id}");
            if (!array_key_exists($tribunal, $tribunais)) {
                $tribunais[$tribunal] = [];
            }
            if (!array_key_exists('updates', $tribunais[$tribunal])) {
                $tribunais[$tribunal]['updates'] = [];
            }
            if (!array_key_exists('news', $tribunais[$tribunal])) {
                $tribunais[$tribunal]['news'] = [];
            }
            if ($created_at) {
                if (count($tribunais[$tribunal]['news']) < 10) {
                    //get the original with $log->tema_id
                    $original = $this->get_original_by_item_id($tribunal, $log->item_id, $log->tipo);
                    if (empty($original)) {
                        continue;
                    }
                    if (empty($original->texto)) {
                        $original->texto = $original->tese_texto ?? "";
                    }
                    if (empty($original->texto)) {
                        $original->texto = $original->tese ?? "";
                    }
                    if (empty($original->texto)) {
                        $original->texto = $original->tema_texto ?? "";
                    }
                    if (empty($original->texto)) {
                        $original->texto = $original->tema ?? "";
                    }
                    $log->original = $original;
                    $tribunais[$tribunal]['news'][] = $log;
                }
            } else {
                if (count($tribunais[$tribunal]['updates']) < 10) {
                    $tribunais[$tribunal]['updates'][] = $log;
                }
            }
        }

        //dd($tribunais);

        $description = 'Recentes atualizações de Teses de Repercussão e Repetitivos e de Súmulas dos tribunais superiores (STF, STJ, TST) e de outros órgãos federais relevantes (TNU, FONAJE/CNJ, CEJ/CJF, TCU, CARF)';
        return view('front.atualizacoes', compact('tribunais', 'display_pdf', 'description'));
    } //end public function
}