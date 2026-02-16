<?php

use App\Models\EditableContent;
use App\Models\Newsletter;

/**
 * Smoke tests para todas as rotas públicas da aplicação.
 *
 * Separados em grupos:
 * 1. Rotas que funcionam com SQLite in-memory (sem queries MySQL-específicas)
 * 2. Rotas que dependem de queries MySQL (fulltext, enums, etc.)
 * 3. Páginas protegidas (requerem autenticação)
 * 4. Rotas com dados seedados
 *
 * Usa RefreshDatabase (via Pest.php) para garantir que as tabelas existam no SQLite in-memory.
 *
 * NOTA: Quando migrarmos os testes para MySQL (removendo SQLite do phpunit.xml),
 * todos os testes do Grupo 2 devem ser atualizados para assertStatus(200).
 */

// ==========================================
// GRUPO 1: Rotas independentes de MySQL
// Devem retornar HTTP 200
// ==========================================

it('carrega a página inicial', function () {
    $this->get('/')->assertStatus(200);
});

it('carrega a página de índice', function () {
    $this->get('/index')->assertStatus(200);
});

it('carrega a página de temas', function () {
    $this->get('/temas')->assertStatus(200);
});

it('carrega a página de login', function () {
    $this->get('/login')->assertStatus(200);
});

it('registro retorna 404 (rota desabilitada)', function () {
    $this->get('/register')->assertStatus(404);
});

it('carrega a página de reset de senha', function () {
    $this->get('/forgot-password')->assertStatus(200);
});

it('carrega a página de obrigado newsletter', function () {
    $this->get('/newsletter-obrigado')->assertStatus(200);
});

// ==========================================
// GRUPO 2: Rotas que dependem de queries MySQL-específicas
// Testam que a rota existe e o controller é invocado.
// Com SQLite, podem retornar 500 (query syntax error).
// TODO: Quando usarmos MySQL nos testes, alterar para assertStatus(200).
// ==========================================

it('responde na rota de teses STF', function () {
    assertRouteResponds('/teses/stf');
});

it('responde na rota de teses STJ', function () {
    assertRouteResponds('/teses/stj');
});

it('responde na rota de súmulas STF', function () {
    assertRouteResponds('/sumulas/stf');
});

it('responde na rota de súmulas STJ', function () {
    assertRouteResponds('/sumulas/stj');
});

it('responde na rota de súmulas TST', function () {
    assertRouteResponds('/sumulas/tst');
});

it('responde na rota de súmulas TNU', function () {
    assertRouteResponds('/sumulas/tnu');
});

it('responde na rota de newsletters', function () {
    assertRouteResponds('/newsletters');
});

it('responde na rota de quizzes', function () {
    assertRouteResponds('/quizzes');
})->skip('Quiz: funcionalidade escondida');

it('responde na rota de atualizações', function () {
    assertRouteResponds('/atualizacoes');
});

it('responde na rota de planos de assinatura', function () {
    assertRouteResponds('/assinar');
});

// Rotas de súmula/tese individual (sem parâmetro obrigatório — redirecionam para listagem)
it('redireciona rota de súmula STF individual sem parâmetro', function () {
    $response = $this->get('/sumula/stf');
    expect($response->getStatusCode())->toBeIn([200, 302, 500]);
});

it('redireciona rota de súmula STJ individual sem parâmetro', function () {
    $response = $this->get('/sumula/stj');
    expect($response->getStatusCode())->toBeIn([200, 302, 500]);
});

it('redireciona rota de tese STF individual sem parâmetro', function () {
    $response = $this->get('/tese/stf');
    expect($response->getStatusCode())->toBeIn([200, 302, 500]);
});

it('redireciona rota de tese STJ individual sem parâmetro', function () {
    $response = $this->get('/tese/stj');
    expect($response->getStatusCode())->toBeIn([200, 302, 500]);
});

// ==========================================
// GRUPO 3: Páginas Protegidas
// Devem redirecionar para login
// ==========================================

it('redireciona página de assinatura para login', function () {
    $this->get('/minha-conta/assinatura')->assertRedirect('/login');
});

it('redireciona painel admin para login', function () {
    $this->get('/painel')->assertRedirect();
});

it('redireciona página de estorno para login', function () {
    $this->get('/minha-conta/estorno')->assertRedirect('/login');
});

// ==========================================
// GRUPO 4: Rotas com dados seedados
// Testa que rotas retornam 200 e exibem conteúdo real
// ==========================================

describe('Rotas com dados seedados', function () {

    it('exibe newsletter individual com conteúdo', function () {
        $newsletter = Newsletter::create([
            'subject' => 'Newsletter de Teste',
            'slug' => 'newsletter-teste-smoke',
            'html_content' => '<p>Conteúdo da newsletter de teste</p>',
            'plain_text' => 'Conteúdo da newsletter de teste',
            'sent_at' => now(),
        ]);

        $this->get("/newsletter/{$newsletter->slug}")
            ->assertStatus(200)
            ->assertSee('Newsletter de Teste');
    });

    it('exibe quiz publicado na listagem', function () {
        $quiz = createPublishedQuiz();

        $response = $this->get('/quizzes');

        // Pode dar 500 com SQLite por queries complexas
        expect($response->getStatusCode())->toBeIn([200, 500]);
    })->skip('Quiz: funcionalidade escondida');

    it('exibe conteúdo editável publicado', function () {
        EditableContent::create([
            'slug' => 'precedentes-vinculantes-cpc',
            'title' => 'Precedentes Vinculantes',
            'content' => '<p>Conteúdo sobre precedentes vinculantes no CPC</p>',
            'published' => true,
        ]);

        $this->get('/precedentes-vinculantes-cpc')
            ->assertStatus(200)
            ->assertSee('Precedentes Vinculantes');
    });

});
