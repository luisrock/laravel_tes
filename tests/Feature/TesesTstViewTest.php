<?php

test('renderiza tese antes dos metadados na listagem de teses TST', function () {
    $teses = collect([
        (object) [
            'id' => 1,
            'numero' => '121',
            'tema_pure_text' => 'RR-0000473-37.2024.5.05.0371 Acórdão (Publicado em 9/5/2025)',
            'tese_texto' => 'O auxílio-alimentação não tem natureza salarial quando o empregado contribui para o custeio.',
        ],
    ]);

    $html = view('front.teses_tst', [
        'tribunal' => 'TST',
        'teses' => $teses,
        'count' => $teses->count(),
        'label' => 'Teses Vinculantes do Tribunal Superior do Trabalho - TST',
        'description' => 'Descrição de teste',
        'admin' => false,
        'display_pdf' => false,
        'breadcrumb' => [],
    ])->render();

    $posTese = strpos($html, 'O auxílio-alimentação não tem natureza salarial quando o empregado contribui para o custeio.');
    $posMetadados = strpos($html, 'RR-0000473-37.2024.5.05.0371 Acórdão (Publicado em 9/5/2025)');

    expect($posTese)->not->toBeFalse();
    expect($posMetadados)->not->toBeFalse();
    expect($posTese)->toBeLessThan($posMetadados);
});
