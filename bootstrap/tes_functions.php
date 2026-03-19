<?php

// TODO: ordenar resultados STF via db e excluir canceladas e revogadas

function search_query_parser()
{
    static $parser;

    if (! $parser) {
        $parser = new \App\Services\SearchQueryParser;
    }

    return $parser;
}

function search_database_service()
{
    static $service;

    if (! $service) {
        $service = app(\App\Services\SearchDatabaseService::class);
    }

    return $service;
}

function adjustOneQuoteOnly($str)
{
    return search_query_parser()->adjustOneQuoteOnly((string) $str);
}

function get_teses_with_ai($tribunal = null)
{
    return \Illuminate\Support\Facades\Cache::remember('ai_teses_'.($tribunal ?? 'all'), 3600, function () use ($tribunal) {
        $query = \Illuminate\Support\Facades\DB::table('tese_analysis_sections')->select('tese_id');
        if ($tribunal) {
            $query->where('tribunal', $tribunal);
        }
        $ids = $query->pluck('tese_id')->toArray();
        $final_ids = [];
        foreach (array_unique($ids) as $id) {
            $final_ids[] = (int) $id;
            $final_ids[] = (string) $id;
        }

        return $final_ids;
    });
}

function noSignal($str)
{
    return search_query_parser()->noSignal((string) $str);
}

function adjustOperators($keyword)
{
    return search_query_parser()->adjustOperators((string) $keyword);
}

function signalString($array, $i)
{
    return search_query_parser()->signalString($array, (int) $i);
}

function keyword_to_array($keyword)
{
    return search_query_parser()->keywordToArray((string) $keyword);
} // end function
// END functions for mysql search (STJ, TNU)

function trib_format_date($date)
{
    $date_raw = date_create($date);

    return date_format($date_raw, 'd/m/Y');
}

function trib_remove_substring_after_delim($s, $delim)
{
    return (strpos($s, $delim)) ? trim(substr($s, 0, strpos($s, $delim))) : $s;
}

function trib_remove_new_line($s)
{
    $s = str_replace("\r\n", '', $s);
    $s = str_replace("\n", '', $s);
    $s = str_replace("\r", '', $s);

    return trim($s);
}

function trib_request_body($data, $tribunal, $url = null, $verify = false)
{
    if (! $url) {
        $url = config('tes_constants.options.'.strtolower($tribunal).'_search_url');
    }

    $headers = [
        'Pragma' => 'no-cache',
        'Cache-Control' => 'no-cache',
        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.102 Safari/537.36',
        'Content-Type' => 'application/json',
        'Accept' => '*/*',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
    ];
    $options = [
        'verify' => $verify,
    ];

    $request = Http::withHeaders($headers)
        ->timeout(15)
        ->withOptions($options)
        ->post($url, $data);

    // $request->throw();
    if ($request->failed()) {
        return $request->status();
    }

    $body = $request->json();
    if (empty($body)) {
        return 'Não foi possível recuperar o corpo da requisição';
    }

    return $body;
}

function stf_request($keyword)
{
    // montando o array a ser entregue ao final
    $stf_content = [];

    // bases de buscas (repercussão, só com mérito julgado)
    // TODO: acrescer outras, a gosto (mas providenciar o arquivo json correlato para o request)
    $com_resultados = false;

    foreach (['sumula', 'tese'] as $s) {

        $output[$s] = [];

        if ($s == 'tese') {
            $file = storage_path('stf_json/repercussao.json');
            $base_label = 'TESES DE REPERCUSSÃO GERAL';
        } elseif ($s == 'sumula') {
            $file = storage_path('stf_json/sumula.json');
            $base_label = 'SÚMULAS';
        }

        // Carregando o arquivo json com o payload para o request
        $json = file_get_contents($file);
        // Convertendo em array
        $j = json_decode($json, true);

        // Inserindo os termos de busca no payload
        $j['query']['function_score']['query']['bool']['filter'][0]['query_string']['query'] = $keyword;
        $j['query']['function_score']['query']['bool']['should'][0]['query_string']['query'] = $keyword;
        $j['query']['function_score']['query']['bool']['should'][1]['query_string']['query'] = $keyword;
        $j['query']['function_score']['query']['bool']['should'][2]['query_string']['query'] = $keyword;
        $j['query']['function_score']['query']['bool']['should'][3]['query_string']['query'] = $keyword;
        $j['highlight']['highlight_query']['bool']['filter'][0]['query_string']['query'] = $keyword;
        $j['highlight']['highlight_query']['bool']['should'][0]['query_string']['query'] = $keyword;
        $j['highlight']['highlight_query']['bool']['should'][1]['query_string']['query'] = $keyword;
        $j['highlight']['highlight_query']['bool']['should'][2]['query_string']['query'] = $keyword;
        $j['highlight']['highlight_query']['bool']['should'][3]['query_string']['query'] = $keyword;

        // var_dump($j);
        // exit;

        // Fazendo a requisição
        $response = trib_request_body($j, 'stf');

        if (is_int($response)) {
            return "O sistema do STF pode estar momentaneamente indisponível (ou sua consulta foi mal construída). Código de resposta da requisição => $response";
        }
        if (empty($response)) {
            return 'Requisição ao STF falhou...';
            exit;
        }

        // return $response; }

        $total = $response['result']['hits']['total']['value']; // total de julgados colhidos
        $lista = $response['result']['hits']['hits']; // lista com os julgados

        $output[$s]['total'] = $total;
        $output[$s]['hits'] = [];
        if (empty($lista)) {
            continue;
        }

        foreach ($lista as $item) {
            if (empty($item['_source'])) {
                continue;
            }
            $julgado = $item['_source'];

            // colheita para sumulas
            if ($s == 'sumula') {
                $sum_array = [];
                $sum_array['trib_sum_titulo'] = (! empty($julgado['titulo'])) ? str_replace('vinculante', 'Vinculante', $julgado['titulo']) : '';
                $sum_array['trib_sum_numero'] = $julgado['sumula_numero'] ?? '';
                $sum_array['trib_sum_vinculante'] = $julgado['is_vinculante'] ?? '';
                //           $sum_array['trib_sum_texto'] = mj_remove_new_line(julgado.get('sumula_texto', ''))
                $sum_array['trib_sum_texto'] = $julgado['sumula_texto'] ?? '';
                //           $sum_array['trib_sum_data'] = mj_format_datetime(julgado.get('julgamento_data', ''),'%Y-%m-%d')
                $sum_array['trib_sum_data'] = trib_format_date($julgado['julgamento_data']) ?? '';

                $sum_array['trib_sum_url'] = 'https://jurisprudencia.stf.jus.br/pages/search/'.$julgado['id'].'/false';

                // find sumula id on the stf_sumulas table by numero
                $sumula_id = DB::table('stf_sumulas')
                    ->select('id')
                    ->where('numero', $sum_array['trib_sum_numero'])
                    ->first();
                $sum_array['trib_sum_id'] = ($sumula_id) ? $sumula_id->id : '';

                $output[$s]['hits'][] = $sum_array;
            } // end if sumula

            // colheita para tese
            if ($s == 'tese') {

                $rep_array = [];

                if (
                    isset($julgado['documental_tese_tema_texto'])
                    &&
                    isset($julgado['documental_tese_texto'])
                    &&
                    (empty($julgado['documental_tese_tema_texto']) || $julgado['documental_tese_tema_texto'] == 'None')
                    &&
                    (empty($julgado['documental_tese_texto']) || $julgado['documental_tese_texto'] == 'None')
                ) {
                    // sem tese e sem tema. Prosseguir para a próxima...
                    continue;
                }

                $tema = $julgado['documental_tese_tema_texto'] ?? '';
                $tese = $julgado['documental_tese_texto'] ?? '';
                if ($tese) {
                    $tese = trib_remove_substring_after_delim($tese, 'Obs:');
                    $tese = trib_remove_new_line($tese);
                }
                if ($tema) {
                    $tema = trib_remove_new_line($tema);
                }
                $julgamento_data = trib_format_date($julgado['julgamento_data']) ?? '';

                $rep_array['trib_rep_titulo'] = $julgado['titulo'] ?? '';
                $rep_array['trib_rep_relator'] = $julgado['relator_processo_nome'] ?? '';
                $rep_array['trib_rep_tema'] = $tema;
                $rep_array['trib_rep_tese'] = $tese;
                $rep_array['trib_rep_data'] = $julgamento_data;
                $rep_array['trib_rep_url'] = $julgado['inteiro_teor_url'] ?? '';
                $rep_array['trib_rep_numero'] = trib_remove_substring_after_delim($tema, '-') ?? '';

                // find tese id on the stf_teses table by numero
                $tese_id = DB::table('stf_teses')
                    ->select('id')
                    ->where('numero', $rep_array['trib_rep_numero'])
                    ->first();
                $rep_array['trib_rep_id'] = ($tese_id) ? $tese_id->id : '';

                $output[$s]['hits'][] = $rep_array;
            } // end if tese
        } // end foreach lista:
    } // end foreach s

    $output['total_count'] = $output['sumula']['total'] + $output['tese']['total'];

    return $output;
}

function tst_request($keyword)
{

    // montando o array a ser entregue ao final
    $output = [];
    $output['sumula'] = [];
    $output['sumula']['total'] = 0;
    $output['sumula']['hits'] = [];
    $output['orientacao_precedente'] = [];
    $output['orientacao_precedente']['total'] = 0;
    $output['orientacao_precedente']['hits'] = [];

    $search = ['SUM', 'OJ', 'PN'];

    // Carregando o arquivo json com o payload para o request
    $file = storage_path('tst_json/all.json');
    $json = file_get_contents($file);
    // Convertendo em array
    $j = json_decode($json, true);
    // chamando a keyword
    $j['e'] = $keyword;

    // Fazendo a requisição
    $response = trib_request_body($j, 'tst', null, true);

    if (is_int($response)) {
        return "O sistema do TST pode estar momentaneamente indisponível. Código de resposta da requisição => $response";
    }
    if (empty($response)) {
        return 'Requisição ao TST falhou...';
        exit;
    }
    if (! isset($response['agregacoes'][0]['mapaTermoQuantidade'])) {
        //     echo 'Requisição ao TST não retornou mapa de quantidade...';
        return $output;
    }
    if (empty($response['registros'])) {
        //     echo 'Requisição ao TST não retornou registros...';
        return $output;
    }

    $quantidade_mapa = $response['agregacoes'][0]['mapaTermoQuantidade'];

    if (isset($quantidade_mapa['SUM'])) {
        $output['sumula']['total'] = $quantidade_mapa['SUM'];
    }

    if (isset($quantidade_mapa['OJ'])) {
        $output['orientacao_precedente']['total'] += $quantidade_mapa['OJ'];
    }

    if (isset($quantidade_mapa['OJT'])) {
        $output['orientacao_precedente']['total'] += $quantidade_mapa['OJT'];
    }

    if (isset($quantidade_mapa['PN'])) {
        $output['orientacao_precedente']['total'] += $quantidade_mapa['PN'];
    }

    $lista = $response['registros'];

    foreach ($lista as $l) {

        $registro = $l['registro'];
        if (empty($registro['tipo']['codigoTipoJurisprudencia'])) {
            // TODO: diminuir uma do total (mas como saber de onde diminuir, se não há tipo?)
            continue;
        }
        $tipo = $registro['tipo']['codigoTipoJurisprudencia'];

        // keys para o array de hits
        if ($tipo === 'SUM') {
            $array_key = 'sum';
            $output_key = 'sumula';
            $output_tipo = 'Súmula';
        }

        if ($tipo === 'OJ' || $tipo === 'OJT') {
            $array_key = 'rep';
            $output_key = 'orientacao_precedente';
            $output_tipo = 'Orientação Jurisprudencial';
        }

        if ($tipo === 'PN') {
            $array_key = 'rep';
            $output_key = 'orientacao_precedente';
            $output_tipo = 'Precedente Normativo';
        }

        // preenchendo hits
        $item_array = [];
        $item_array["trib_$array_key".'_titulo'] = $registro['titulo'] ?? '';
        $item_array["trib_$array_key".'_numero'] = $registro['numero'] ?? '';
        $item_array["trib_$array_key".'_vinculante'] = $registro['is_vinculante'] ?? '';
        $item_array["trib_$array_key".'_texto'] = $registro['tese'] ?? '';
        $item_array["trib_$array_key".'_data'] = (! empty($registro['dtaPublicacao'])) ? trib_format_date($registro['dtaPublicacao']) : '';
        $item_array["trib_$array_key".'_situacao'] = $registro['situacao']['descricao'] ?? '';
        $item_array["trib_$array_key".'_url'] = '';
        $item_array["trib_$array_key".'_tipo'] = $output_tipo;

        if (! empty($item_array["trib_$array_key".'_situacao']) && strtolower($item_array["trib_$array_key".'_situacao']) != 'cancelada') {
            $output[$output_key]['hits'][] = $item_array;
        } else {
            // diminuir uma do total
            $output[$output_key]['total'] -= 1;
        }

    } // end foreach lista

    // TODO: total count key
    return $output;
}

function tcu_request($keyword)
{

    $output = [];

    $com_resultados = false;

    foreach (['sumula', 'tese'] as $s) {

        $output[$s] = [];
        $search_tcu_url_base = 'https://pesquisa.apps.tcu.gov.br/rest/publico/base';
        $type = ($s === 'sumula') ? $s : 'resposta-consulta';
        $search_tcu_url = "$search_tcu_url_base/$type/documentosResumidos?";

        $params = [
            'termo' => $keyword,
            'ordenacao' => 'DTRELEVANCIA desc, NUMEROINT desc',
            'quantidade' => '200',
            'inicio' => '0',
            'sinonimos' => 'true',
        ];

        if ($s === 'tese') {
            $params['ordenacao'] = 'score desc, COLEGIADO asc, ANOACORDAO desc, NUMACORDAO desc';
        } elseif ($s === 'sumula') {
            $params['filtro'] = 'VIGENTE:"true"';
        }

        $final_url = $search_tcu_url.http_build_query($params);

        $headers = [
            'Pragma' => 'no-cache',
            'Cache-Control' => 'no-cache',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.102 Safari/537.36',
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
        ];

        $request = Http::withHeaders($headers)
            ->timeout(15)
            ->get($final_url);

        if ($request->failed()) {
            return 'O sistema do TCU pode estar momentaneamente indisponível. Código de resposta da requisição => '.$request->status();
        }
        if (empty($request)) {
            return 'Requisição ao TCU falhou...';
        }

        $body = $request->body();
        if (empty($body)) {
            return 'Não foi possível recuperar o corpo da requisição';
        }
        $response = json_decode($body, true);

        if (empty($response)) {
            return 'Não foi possível obter uma resposta apropriada da requisição';
        }

        $total = $response['quantidadeEncontrada']; // total de julgados colhidos
        $lista = $response['documentos']; // lista com os julgados

        $output[$s]['total'] = $total;
        $output[$s]['hits'] = [];
        if (empty($lista)) {
            continue;
        }
        $i = 0;
        foreach ($lista as $julgado) {

            // colheita para sumulas
            if ($s == 'sumula') {
                $sum_array = [];
                $sum_array['trib_sum_titulo'] = (! empty($julgado['TITULO'])) ? str_replace(':', '', $julgado['TITULO']) : '';
                $sum_array['trib_sum_numero'] = $julgado['NUMERO'] ?? '';
                $sum_array['trib_sum_texto'] = $julgado['CABECALHO'] ?? '';
                $sum_array['trib_sum_enunciado'] = $julgado['ENUNCIADO'] ?? '';
                $sum_array['trib_sum_data'] = $julgado['ANOAPROVACAO'] ?? '';
                $sum_array['trib_sum_vigente'] = $julgado['VIGENTE'] ?? '';
                $key_encoded = rawurlencode($keyword);
                $sum_array['trib_sum_url'] = "https://pesquisa.apps.tcu.gov.br/#/documento/sumula/{$key_encoded}/VIGENTE%253A%2522true%2522/DTRELEVANCIA%2520desc%252C%2520NUMEROINT%2520desc/{$i}/sinonimos%253Dtrue";

                $output[$s]['hits'][] = $sum_array;
                $i++;
            } // end if sumula

            // colheita para tese
            if ($s == 'tese') {
                $rep_array = [];
                $rep_array['trib_rep_acordao'] = $julgado['NUMACORDAO'] ?? '';
                $rep_array['trib_rep_ano'] = $julgado['ANOACORDAO'] ?? '';
                $rep_array['trib_rep_orgao'] = $julgado['COLEGIADO'] ?? '';
                $rep_array['trib_rep_data'] = $julgado['DATASESSAOFORMATADA'] ?? '';
                $rep_array['trib_rep_autor'] = $julgado['AUTORTESE'] ?? '';
                $rep_array['trib_rep_funcao'] = $julgado['FUNCAOAUTORTESE'] ?? '';
                $rep_array['trib_rep_texto'] = $julgado['ENUNCIADO'] ?? '';
                $rep_array['trib_rep_url'] = "https://pesquisa.apps.tcu.gov.br/#/documento/acordao-completo/*/NUMACORDAO:{$rep_array['trib_rep_acordao']}%20ANOACORDAO:{$rep_array['trib_rep_ano']}%20COLEGIADO:%22{$rep_array['trib_rep_orgao']}%22/DTRELEVANCIA%20desc,%20NUMACORDAOINT%20desc/0/%20";

                $output[$s]['hits'][] = $rep_array;
            } // end if tese
        } // end foreach lista:
    } // end foreach s

    $output['total_count'] = $output['sumula']['total'] + $output['tese']['total'];

    return $output;
}

function adjustingRep($res, $tribunal)
{
    $output = [];
    switch ($tribunal) {
        case 'STF':
            $output['total'] = $res['tese']['total'];
            $output['content'] = $res['tese']['hits'];
            $output['singular'] = 'tese de repercussão geral com mérito julgado encontrada';
            $output['plural'] = 'teses de repercussão geral com mérito julgado encontradas';
            $output['nenhum'] = 'Nenhuma';
            $output['em'] = 'no';
            break;
        case 'TST':
            $output['total'] = $res['orientacao_jurisprudencial']['total'] + $res['precedente_normativo']['total'];
            $output['content'] = array_merge($res['orientacao_jurisprudencial']['hits'], $res['precedente_normativo']['hits']);
            $output['singular'] = 'tese de orientação jurisprudencial ou precedente normativo encontrada';
            $output['plural'] = 'teses de orientação jurisprudencial ou precedente normativo encontradas';
            $output['nenhum'] = 'Nenhuma';
            $output['em'] = 'no';
            break;
        case 'STJ':
            $output['total'] = $res['tese']['total'];
            $output['content'] = $res['tese']['hits'];
            $output['singular'] = 'tema de repetitivo encontrado';
            $output['plural'] = 'temas de repetitivo encontrados';
            $output['nenhum'] = 'Nenhum';
            $output['em'] = 'no';
            break;
        case 'TNU':
            $output['total'] = $res['tese']['total'];
            $output['content'] = $res['tese']['hits'];
            $output['singular'] = 'tema representativo encontrado';
            $output['plural'] = 'temas representativos encontrados';
            $output['nenhum'] = 'Nenhum';
            $output['em'] = 'na';
            break;
        case 'TCU':
            $output['total'] = $res['tese']['total'];
            $output['content'] = $res['tese']['hits'];
            $output['singular'] = 'enunciado paradigmático encontrado';
            $output['plural'] = 'enunciados paradigmáticos encontrados';
            $output['nenhum'] = 'Nenhum';
            $output['em'] = 'no';
            break;
        case 'CARF':
        case 'FONAJE':
        case 'CEJ':
            $output['total'] = $res['tese']['total'];
            $output['content'] = $res['tese']['hits'];
            $output['singular'] = 'tese encontrada';
            $output['plural'] = 'teses encontradas';
            $output['nenhum'] = 'Nenhuma';
            $output['em'] = 'no';
            break;
        default:
            $output['total'] = 0;
            $output['content'] = 'Nenhum tribunal';
            $output['singular'] = 'nenhum tribunal';
            $output['plural'] = 'nenhum tribunal';
            $output['nenhum'] = 'Nenhuma';
            $output['em'] = 'no';
            break;
    } // end switch

    return $output;
} // end function

function insertOperator($arr)
{
    return search_query_parser()->insertOperator($arr);
}

function buildFinalSearchString($new_arr)
{
    return search_query_parser()->buildFinalSearchString($new_arr);
}

function buildFinalSearchStringForApi($keyword, $tribunal)
{
    return search_query_parser()->buildFinalSearchStringForApi((string) $keyword, (string) $tribunal);
}

// Adjust STF queries

function stf_adjust_query_sum($results)
{
    $array = [];
    foreach ($results as $r) {
        $a_r = [];
        $a_r['trib_sum_titulo'] = $r['titulo'] ?? '';
        $a_r['trib_sum_texto'] = $r['texto'] ?? '';
        $a_r['trib_sum_data'] = $r['aprovadaEm'] ?? '';
        $a_r['trib_sum_url'] = $r['link'] ?? '';
        $a_r['trib_sum_id'] = $r['id'] ?? '';
        $a_r['trib_sum_numero'] = $r['numero'] ?? '';
        $a_r['trib_sum_vinculante'] = $r['is_vinculante'] ?? 0;

        $array[] = $a_r;
    } // end foreach

    return $array;
}

function stf_adjust_query_rep($results)
{
    $array = [];
    foreach ($results as $r) {
        $a_r = [];
        $a_r['trib_rep_titulo'] = $r['acordao'] ?? '';
        $a_r['trib_rep_tema'] = $r['tema_texto'] ?? '';
        $a_r['trib_rep_tese'] = $r['tese_texto'] ?? '';
        $a_r['trib_rep_relator'] = $r['relator'] ?? '';
        $a_r['trib_rep_data'] = $r['aprovadaEm'] ?? '';
        $a_r['trib_rep_url'] = $r['link'] ?? '';
        $a_r['trib_rep_id'] = $r['id'] ?? '';
        $a_r['trib_rep_numero'] = $r['numero'] ?? '';
        $array[] = $a_r;
    } // end foreach

    return $array;
}

// Adjust TST queries
function tst_adjust_query_sum($results)
{
    $array = [];
    foreach ($results as $r) {
        $a_r = [];
        if (strpos($r['tema'], 'cancelamento') !== false || strpos($r['tema'], 'cancelada') !== false) {
            continue;
        }
        $a_r['trib_sum_tipo'] = 'Súmula';
        $a_r['trib_sum_numero'] = $r['numero'] ?? '';
        $a_r['trib_sum_tema'] = $r['tema'] ?? '';
        $a_r['trib_sum_texto'] = $r['texto'] ?? '';
        $a_r['trib_sum_url'] = $r['link'] ?? '';
        $a_r['trib_sum_id'] = $r['id'] ?? '';
        $array[] = $a_r;
    } // end foreach

    return $array;
}

function tst_adjust_query_rep($results)
{
    $array = [];
    foreach ($results as $r) {
        $a_r = [];
        if (
            strpos($r['tema'], 'cancelamento') !== false ||
            strpos($r['tema'], 'cancelada') !== false ||
            strpos($r['tema'], 'cancelado') !== false ||
            strpos($r['tema'], 'negativo') !== false
        ) {
            continue;
        }
        $a_r['trib_rep_numero'] = $r['numero'] ?? '';
        $a_r['trib_rep_tipo'] = ($r['tipo'] == 'PN') ? 'Precedente Normativo' : 'Orientação Jurisprudencial';
        $a_r['trib_rep_tema'] = $r['tema'] ?? '';
        $a_r['trib_rep_texto'] = $r['texto'] ?? '';
        $a_r['trib_rep_url'] = $r['link'] ?? '';
        $a_r['trib_rep_id'] = $r['id'] ?? '';
        $array[] = $a_r;
    } // end foreach

    return $array;
}

// Adjust STJ queries

function stj_adjust_query_sum($results)
{
    $array = [];
    foreach ($results as $r) {
        $a_r = [];
        if (! empty($r['isCancelada'])) {
            continue;
        }
        $a_r['trib_sum_titulo'] = $r['titulo'] ?? '';
        $a_r['trib_sum_numero'] = $r['numero'] ?? '';
        $a_r['trib_sum_texto'] = $r['texto'] ?? '';
        $a_r['trib_sum_data'] = $r['julgadaEm'] ?? '';
        $a_r['trib_sum_is_cancelada'] = $r['isCancelada'] ?? '';
        $a_r['trib_sum_dados'] = $r['dados'] ?? '';
        $a_r['trib_sum_url'] = '#';
        $a_r['trib_sum_id'] = $r['id'] ?? '';
        $array[] = $a_r;
    } // end foreach

    return $array;
}

function stj_adjust_query_rep($results)
{
    $array = [];
    foreach ($results as $r) {
        $a_r = [];
        if (strtolower($r['situacao']) === 'cancelado') {
            continue;
        }
        $a_r['trib_rep_numero'] = $r['numero'] ?? '';
        $a_r['trib_rep_orgao'] = $r['orgao'] ?? '';
        $a_r['trib_rep_tema'] = $r['tema'] ?? '';
        $a_r['trib_rep_tese'] = $r['tese_texto'] ?? '';
        $a_r['trib_rep_situacao'] = $r['situacao'] ?? '';
        $a_r['trib_rep_data'] = $r['atualizadaEm'] ?? '';
        $a_r['trib_rep_url'] = "http://www.stj.jus.br/repetitivos/temas_repetitivos/pesquisa.jsp?novaConsulta=true&tipo_pesquisa=T&cod_tema_inicial={$r['numero']}&cod_tema_final={$r['numero']}";
        $a_r['trib_rep_id'] = $r['id'] ?? '';
        $array[] = $a_r;
    } // end foreach

    return $array;
}

// Adjust TNU queries

function tnu_adjust_query_sum($results)
{
    $array = [];
    foreach ($results as $r) {
        $a_r = [];
        if (! empty($r['isCancelada'])) {
            continue;
        }
        $a_r['trib_sum_titulo'] = $r['titulo'] ?? '';
        $a_r['trib_sum_numero'] = $r['numero'] ?? '';
        $a_r['trib_sum_texto'] = $r['texto'] ?? '';
        $a_r['trib_sum_is_cancelada'] = $r['isCancelada'] ?? '';
        $a_r['trib_sum_dados'] = $r['dados'] ?? '';
        $a_r['trib_sum_url'] = $r['link'] ?? '';
        $a_r['trib_sum_id'] = $r['id'] ?? '';
        $array[] = $a_r;
    } // end foreach

    return $array;
}

function tnu_adjust_query_rep($results)
{
    $array = [];
    foreach ($results as $r) {
        $a_r = [];
        if (empty($r['tese'])) {
            continue;
        }

        $a_r['trib_rep_numero'] = $r['numero'] ?? '';
        $a_r['trib_rep_titulo'] = $r['titulo'] ?? '';
        $a_r['trib_rep_tema'] = $r['tema'] ?? '';
        $a_r['trib_rep_tese'] = $r['tese'] ?? '';
        $a_r['trib_rep_relator'] = $r['relator'] ?? '';
        $a_r['trib_rep_processo'] = $r['processo'] ?? '';
        $a_r['trib_rep_situacao'] = $r['situacao'] ?? '';
        $a_r['trib_rep_data'] = $r['julgadoEm'] ?? '';
        $a_r['trib_rep_url'] = $r['link'] ?? '';
        $a_r['trib_rep_transito'] = $r['transito'] ?? '';
        $a_r['trib_rep_obs'] = $r['obs'] ?? '';
        $a_r['trib_rep_id'] = $r['id'] ?? '';
        $array[] = $a_r;
    } // end foreach

    return $array;
}

// Adjust TCU queries

// Adjust CARF queries
function carf_adjust_query_sum($results)
{
    $array = [];
    foreach ($results as $r) {
        $a_r = [];
        $a_r['trib_sum_titulo'] = $r['titulo'] ?? '';
        $a_r['trib_sum_numero'] = $r['numero'] ?? '';
        $a_r['trib_sum_texto'] = $r['texto'] ?? '';
        $a_r['trib_sum_dados'] = $r['dados'] ?? '';
        $a_r['trib_sum_url'] = '';
        $a_r['trib_sum_id'] = $r['id'] ?? '';
        $array[] = $a_r;
    } // end foreach

    return $array;
}

// Adjust FONAJE queries

function fonaje_adjust_query_sum($results)
{
    $array = [];
    foreach ($results as $r) {
        $a_r = [];
        $a_r['trib_sum_titulo'] = $r['titulo'] ?? '';
        $a_r['trib_sum_numero'] = $r['numero'] ?? '';
        $a_r['trib_sum_texto'] = $r['texto'] ?? '';
        $a_r['trib_sum_dados'] = $r['dados'] ?? '';
        $a_r['trib_sum_url'] = '';
        $a_r['trib_sum_id'] = $r['id'] ?? '';
        $array[] = $a_r;
    } // end foreach

    return $array;
}

// Adjust CEJ queries
function cej_adjust_query_sum($results)
{
    $array = [];
    foreach ($results as $r) {
        $a_r = [];
        $a_r['trib_sum_titulo'] = $r['titulo'] ?? '';
        $a_r['trib_sum_numero'] = $r['numero'] ?? '';
        $a_r['trib_sum_texto'] = $r['texto'] ?? '';
        $a_r['trib_sum_comissao'] = $r['comissao'] ?? '';
        $a_r['trib_sum_ramos'] = $r['ramos'] ?? '';
        $a_r['trib_sum_jornada'] = $r['jornada'] ?? '';
        $a_r['trib_sum_notas'] = $r['notas'] ?? '';
        $a_r['trib_sum_legis'] = $r['legis'] ?? '';
        $a_r['trib_sum_url'] = $r['link'] ?? '';
        $a_r['trib_sum_id'] = $r['id'] ?? '';
        $array[] = $a_r;
    } // end foreach

    return $array;
}

// Calling adjust query functions dinamically
function call_adjust_query_function($tribunal_lower, $kind, $param)
{
    $function_name = trim($tribunal_lower).'_adjust_query_'.trim($kind);

    return $function_name($param);
}

// Calling request API dinamically
function call_request_api($tribunal_lower, $param)
{
    $function_name = trim($tribunal_lower).'_request';

    return $function_name($param);
}

// Searching db (main function)
function tes_search_db($keyword, $tribunal_lower, $tribunal_array)
{
    return search_database_service()->search((string) $keyword, (string) $tribunal_lower, $tribunal_array);
}

// Função auxiliar que executa a busca (extraída para ser usada com ou sem cache)
function tes_search_db_execute($keyword, $tribunal_lower, $tribunal_array)
{
    return search_database_service()->execute((string) $keyword, (string) $tribunal_lower, $tribunal_array);
}

function slugify($text)
{

    if (empty($text)) {
        return false;
    }

    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);

    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    // trim
    $text = trim($text, '-');

    // remove duplicate -
    $text = preg_replace('~-+~', '-', $text);

    // lowercase
    $text = strtolower($text);

    if (empty($text)) {
        return false;
    }

    return $text;
}
