<?php

test('renderiza listagem de teses TNU com dados corretos', function () {
    $teses = collect([
        (object) [
            'id' => 1,
            'numero' => '1',
            'ramo' => 'DIREITO PREVIDENCIÁRIO',
            'situacao' => 'Julgado',
            'isCancelada' => false,
            'tese_texto' => 'O valor da aposentadoria por invalidez será obtido por meio da média aritmética simples.',
            'tema_pure_text' => 'Saber qual a forma de cálculo da aposentadoria por invalidez.',
            'tempo' => 'Julgado em 02/08/2011',
        ],
        (object) [
            'id' => 2,
            'numero' => '389',
            'ramo' => 'DIREITO PREVIDENCIÁRIO',
            'situacao' => 'Afetado',
            'isCancelada' => false,
            'tese_texto' => '',
            'tema_pure_text' => 'Saber se a regra geral de cálculo do salário-de-benefício.',
            'tempo' => '',
        ],
    ]);

    $html = view('front.teses_tnu', [
        'tribunal' => 'TNU',
        'teses' => $teses,
        'count' => $teses->count(),
        'label' => 'Temas Representativos de Controvérsia da Turma Nacional de Uniformização - TNU',
        'description' => 'Descrição de teste',
        'admin' => false,
        'display_pdf' => false,
        'tese_route' => 'tnutesepage',
        'breadcrumb' => [],
    ])->render();

    // Verifica elementos essenciais
    expect($html)->toContain('TEMA 1');
    expect($html)->toContain('TEMA 389');
    expect($html)->toContain('DIREITO PREVIDENCIÁRIO');
    expect($html)->toContain('Julgado');
    expect($html)->toContain('Afetado');
    expect($html)->toContain('média aritmética simples');
    expect($html)->toContain('[aguarda julgamento]');
});

test('renderiza tese antes dos metadados na listagem TNU', function () {
    $teses = collect([
        (object) [
            'id' => 1,
            'numero' => '1',
            'ramo' => 'DIREITO PREVIDENCIÁRIO',
            'situacao' => 'Julgado',
            'isCancelada' => false,
            'tese_texto' => 'O valor da aposentadoria por invalidez será obtido por meio da média.',
            'tema_pure_text' => 'Saber qual a forma de cálculo da aposentadoria.',
            'tempo' => 'Julgado em 02/08/2011',
        ],
    ]);

    $html = view('front.teses_tnu', [
        'tribunal' => 'TNU',
        'teses' => $teses,
        'count' => $teses->count(),
        'label' => 'Temas Representativos TNU',
        'description' => 'Descrição',
        'admin' => false,
        'display_pdf' => false,
        'tese_route' => 'tnutesepage',
        'breadcrumb' => [],
    ])->render();

    $posTese = strpos($html, 'O valor da aposentadoria por invalidez será obtido por meio da média.');
    $posQuestao = strpos($html, 'Saber qual a forma de cálculo da aposentadoria.');

    expect($posTese)->not->toBeFalse();
    expect($posQuestao)->not->toBeFalse();
    expect($posQuestao)->toBeLessThan($posTese);
});

test('exibe badge de cancelado para tese cancelada na listagem TNU', function () {
    $teses = collect([
        (object) [
            'id' => 10,
            'numero' => '10',
            'ramo' => 'DIREITO ADMINISTRATIVO',
            'situacao' => 'Cancelado',
            'isCancelada' => true,
            'tese_texto' => 'Tese cancelada de teste.',
            'tema_pure_text' => 'Questão cancelada.',
            'tempo' => '',
        ],
    ]);

    $html = view('front.teses_tnu', [
        'tribunal' => 'TNU',
        'teses' => $teses,
        'count' => $teses->count(),
        'label' => 'Temas TNU',
        'description' => 'Descrição',
        'admin' => false,
        'display_pdf' => false,
        'tese_route' => 'tnutesepage',
        'breadcrumb' => [],
    ])->render();

    expect($html)->toContain('tw-bg-red-100');
    expect($html)->toContain('tw-line-through');
});
