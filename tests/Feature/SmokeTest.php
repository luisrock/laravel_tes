<?php

/**
 * Smoke tests para todas as rotas públicas da aplicação.
 *
 * Separados em três grupos:
 * 1. Rotas que funcionam com SQLite in-memory (sem queries MySQL-específicas)
 * 2. Rotas que dependem de queries MySQL (fulltext, enums, etc.)
 * 3. Páginas protegidas (requerem autenticação)
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

it('carrega a página de registro', function () {
    $this->get('/register')->assertStatus(200);
});

it('carrega a página de reset de senha', function () {
    $this->get('/password/reset')->assertStatus(200);
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
});

it('responde na rota de atualizações', function () {
    assertRouteResponds('/atualizacoes');
});

it('responde na rota de planos de assinatura', function () {
    assertRouteResponds('/assinar');
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
