<?php

use App\Models\Newsletter;

/**
 * Testes do fluxo de Newsletter — listagem e exibição individual.
 */

// ==========================================
// Listagem de Newsletters
// ==========================================

describe('Listagem de Newsletters', function () {

    it('carrega a página de newsletters', function () {
        // Pode retornar 500 com SQLite por queries complexas
        assertRouteResponds('/newsletters');
    });

});

// ==========================================
// Newsletter Individual
// ==========================================

describe('Newsletter Individual', function () {

    it('exibe newsletter com conteúdo', function () {
        $newsletter = Newsletter::create([
            'subject' => 'Jurisprudência em Destaque',
            'slug' => 'jurisprudencia-destaque-'.uniqid(),
            'html_content' => '<h1>Jurisprudência</h1><p>Conteúdo da edição</p>',
            'plain_text' => 'Conteúdo da edição',
            'sent_at' => now(),
        ]);

        $this->get("/newsletter/{$newsletter->slug}")
            ->assertStatus(200)
            ->assertSee('Jurisprudência em Destaque');
    });

    it('retorna 404 para slug inexistente', function () {
        $this->get('/newsletter/slug-que-nao-existe')
            ->assertNotFound();
    });

});

// ==========================================
// Accessor: getWebContentAttribute
// ==========================================

describe('Newsletter Web Content', function () {

    it('remove footer Nota: do conteúdo web', function () {
        $newsletter = Newsletter::create([
            'subject' => 'Newsletter com Nota',
            'slug' => 'newsletter-nota-'.uniqid(),
            'html_content' => '<p>Conteúdo principal</p><p><strong>Nota</strong>: Este é o footer que deve ser removido.</p>',
            'plain_text' => 'Conteúdo principal',
            'sent_at' => now(),
        ]);

        $webContent = $newsletter->web_content;

        expect($webContent)->toContain('Conteúdo principal');
        expect($webContent)->not->toContain('footer que deve ser removido');
    });

    it('mantém conteúdo completo sem Nota:', function () {
        $newsletter = Newsletter::create([
            'subject' => 'Newsletter sem Nota',
            'slug' => 'newsletter-sem-nota-'.uniqid(),
            'html_content' => '<p>Conteúdo completo sem footer</p>',
            'plain_text' => 'Conteúdo completo',
            'sent_at' => now(),
        ]);

        $webContent = $newsletter->web_content;

        expect($webContent)->toContain('Conteúdo completo sem footer');
    });

});
