<?php

test('renderiza card de tese do TST com layout destacado de tese e sem bloco questão', function () {
    $output = [
        'tese' => [
            'hits' => [
                [
                    'trib_rep_tipo' => 'TV',
                    'trib_rep_numero' => '121',
                    'trib_rep_url' => 'https://jurisprudencia-backend2.tst.jus.br/rest/documentos/9c628df3de898ef8cf933fa48da5b8dd',
                    'trib_rep_tema' => 'RR-0000473-37.2024.5.05.0371 Acórdão (Publicado em 9/5/2025) | Status: Transitado em Julgado',
                    'trib_rep_texto' => 'O auxílio-alimentação não tem natureza salarial quando o empregado contribui para o custeio. | Relator(a): Ministro Aloysio Silva Corrêa da Veiga',
                ],
            ],
        ],
    ];

    $html = view('front.results.inners.tst_rep', [
        'output' => $output,
    ])->render();

    expect($html)
        ->toContain('Tema 121')
        ->toContain('Precedentes Vinculantes')
        ->toContain('Acórdão')
        ->toContain('Tese')
        ->toContain('Situação:')
        ->toContain('Transitado em Julgado')
        ->not->toContain('Relator(a):')
        ->not->toContain('Questão');
});

test('mantém texto oculto de cópia no card de tese TST', function () {
    $output = [
        'tese' => [
            'hits' => [
                [
                    'trib_rep_tipo' => 'TV',
                    'trib_rep_numero' => '4',
                    'trib_rep_url' => 'https://jurisprudencia-backend2.tst.jus.br/rest/documentos/eadec6aefb18c506c71c477ebb9418b1',
                    'trib_rep_tema' => 'IRR-1786-24.2015.5.04.0000 | Acórdão',
                    'trib_rep_texto' => 'A multa coercitiva do art. 523, § 1º, do CPC de 2015 não se aplica ao Processo do Trabalho.',
                    'trib_rep_situacao' => 'Transitado em Julgado',
                ],
            ],
        ],
    ];

    $html = view('front.results.inners.tst_rep', [
        'output' => $output,
    ])->render();

    expect($html)
        ->toContain('tes-text-to-be-copied')
        ->toContain('TESE:')
        ->toContain('SITUAÇÃO: Transitado em Julgado');
});

test('ordena os cards de teses do TST por número do tema em ordem decrescente', function () {
    $output = [
        'tese' => [
            'hits' => [
                [
                    'trib_rep_numero' => '4',
                    'trib_rep_url' => 'https://example.com/4',
                    'trib_rep_tema' => 'Metadado 4',
                    'trib_rep_texto' => 'Texto 4',
                ],
                [
                    'trib_rep_numero' => '121',
                    'trib_rep_url' => 'https://example.com/121',
                    'trib_rep_tema' => 'Metadado 121',
                    'trib_rep_texto' => 'Texto 121',
                ],
                [
                    'trib_rep_numero' => '20',
                    'trib_rep_url' => 'https://example.com/20',
                    'trib_rep_tema' => 'Metadado 20',
                    'trib_rep_texto' => 'Texto 20',
                ],
            ],
        ],
    ];

    $html = view('front.results.inners.tst_rep', [
        'output' => $output,
    ])->render();

    $posTema121 = strpos($html, 'Tema 121');
    $posTema20 = strpos($html, 'Tema 20');
    $posTema4 = strpos($html, 'Tema 4');

    expect($posTema121)->not->toBeFalse();
    expect($posTema20)->not->toBeFalse();
    expect($posTema4)->not->toBeFalse();

    expect($posTema121)->toBeLessThan($posTema20);
    expect($posTema20)->toBeLessThan($posTema4);
});
