<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchToDbPesquisas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $keyword;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($keyword)
    {
        $this->keyword = $keyword;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tema = $this->keyword;

        //Don't store keyword with only numbers
        if(is_numeric($tema)) {
            return;
        }

        //1. Search in all dbs for keyword

        $lista_tribunais = Config::get('tes_constants.lista_tribunais');
        $tribunais = array_keys($lista_tribunais);
        $output = [];

        //Getting the results by querying tes db for all tribunais (except the ones with API, excluding STF)
        //TODO: db for all tribunais
        foreach($tribunais as $tribunal) {
            if($lista_tribunais[$tribunal]['db'] === false && $tribunal !== 'STF' ) { 
                continue;
            }
            
            $output_tribunal = [];
            $tribunal_lower = strtolower($tribunal);
            $tribunal_upper = strtoupper($tribunal);
            $tribunal_array = $lista_tribunais[$tribunal_upper];
            $output_tribunal = tes_search_db($tema,$tribunal_lower,$tribunal_array);
            $output[$tribunal_lower] = $output_tribunal;

        } //END foreach

        $total_count = 0;
        foreach($output as $trib) {
            $total_count += $trib['total_count'];
        }

        //3. Insert to table 'pesquisas' keyword and results (#)
        
        if($total_count > 0) {
            DB::table('pesquisas')->insertOrIgnore([
                'keyword' => $tema,
                'results' => $total_count
            ]);
        }    
    }
}
