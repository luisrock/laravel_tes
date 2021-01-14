<?php

return [
    'options' => [
        'stf_search_url' => 'https://jurisprudencia.stf.jus.br/api/search/search',
        'tst_search_url' => 'https://jurisprudencia-backend.tst.jus.br/rest/pesquisa-textual/1/200',
        'tcu_search_url' => 'https://pesquisa.apps.tcu.gov.br/rest/publico/base',
        'operadores' => ['AND', 'OR', 'NOT'],
        'meta_description' => 'Pesquisa simplificada de Teses de Repercussão e Súmulas dos tribunais superiores (STF, STJ, TST) e de outros órgãos relevantes federais (TNU, FONAJE/CNJ, CEJ/CJF, TCU, CARF), com opcional geração de PDF. Ideal para a preparação de aula, decisão, petição, estudo etc.'
    ],
    'lista_tribunais'  => [
        'STF' => [
            'trib_url' => 'https://jurisprudencia.stf.jus.br/pages/search',
            'request' => 'stf_request',
            'to_match_sum' => 'titulo,texto,obs,legis,precedentes',
            'to_match_rep' => 'tema_texto,tese_texto,indexacao,ementa_texto,relator,acordao',
            'tese_name' => 'tese',
            'tables' => [   'sumulas' => ['sumulas'], 
                            'teses'  => ['teses']
                        ],
            'db' => false // pesquisa = busca via API; temas = busca via db
        ],
        'TST' => [
            'trib_url' => 'https://jurisprudencia.tst.jus.br/',
            'request' => 'tst_request',
            'to_match_sum' => 'titulo,tema,texto',
            'to_match_rep' => 'titulo,tema,texto',
            'tese_name' => 'tese',
            'tables' => [
                            'sumulas' => ['sumulas'], 
                            'teses'  => ['teses']
                        ],
            'db' => true
        ],
        'STJ' => [
            'trib_url' => 'https://scon.stj.jus.br/SCON/',
            'request' => 'stj_request',
            'to_match_sum' => 'texto_raw,ramos',
            'to_match_rep' => 'tese_texto,tema,ramos',
            'tese_name' => 'tese',
            'tables' => [   'sumulas' => ['sumulas'], 
                            'teses'  => ['teses']
                        ],
            'db' => true
        ],
        'TNU' => [
            'trib_url' => 'https://www2.cjf.jus.br/jurisprudencia/tnu/',
            'request' => 'tnu_request',
            'to_match_sum' => 'titulo,texto', //serve para QO tb
            'to_match_rep' => 'titulo,tema,tese',
            'tese_name' => 'tese',
            'tables' => [   'sumulas' => ['sumulas','questoesdeordem'], 
                            'teses'  => ['teses']
                        ],
            'db' => true
        ],
        'TCU' => [
            'trib_url' => 'https://pesquisa.apps.tcu.gov.br/#/pesquisa/jurisprudencia',
            'request' => 'tcu_request',
            'to_match_sum' => '',
            'to_match_rep' => '',
            'tese_name' => 'tese',
            'tables' => [],
            'db' => false
        ],
        'CARF' => [
            'trib_url' => 'http://idg.carf.fazenda.gov.br/jurisprudencia/sumulas-carf',
            'request' => 'carf_request',
            'to_match_sum' => 'titulo,texto',
            'to_match_rep' => '',
            'tese_name' => 'tese',
            'tables' => [   'sumulas' => ['sumulas'], 
                            'teses'  => []
                        ],
            'db' => true
        ],
        'FONAJE' => [
            'trib_url' => 'https://www.cnj.jus.br/corregedoria-nacional-de-justica/redescobrindo-os-juizados-especiais/enunciados-fonaje/',
            'request' => 'fonaje_request',
            'to_match_sum' => 'titulo,texto',
            'to_match_rep' => '',
            'tese_name' => 'tese',
            'tables' => [   'sumulas' => ['civ_sumulas','cri_sumulas','faz_sumulas'], 
                            'teses'  => []
                        ],
            'db' => true
        ],
        'CEJ' => [
            'trib_url' => 'https://www.cjf.jus.br/enunciados/',
            'request' => 'cej_request',
            'to_match_sum' => 'comissao,ramos,texto,legis,notas,titulo,jornada',
            'to_match_rep' => '',
            'tese_name' => 'tese',
            'tables' => [   'sumulas' => ['sumulas'], 
                            'teses'  => []
                        ],
            'db' => true
        ],
    ],
    'sem_tese' => [
        'CARF',
        'FONAJE',
        'CEJ'
    ],
    'sem_sumula' => [],


];


/*
And you can access them as follows

Config::get('constants.options.stf_search_url');
// or
config('constants.options.stf_search_url');
*/

// if(!defined('BASE_PATH')) {
//     if(strpos($_SERVER['DOCUMENT_ROOT'], 'phpSites') === false && ($_SERVER['REMOTE_ADDR'] == '::1' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1') ) {
//       //localhost with MAMPS
//       define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/tesesesumulas/');
//     } else {
//       //production or localhost with laravel Valet on DescoMac
//       define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/' );
//     }
//   } 