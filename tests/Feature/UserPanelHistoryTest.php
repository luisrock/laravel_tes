<?php

use App\Models\ContentView;
use App\Models\User;

// ==========================================
// Acesso ao Painel do Usuário
// ==========================================

describe('Acesso ao painel do usuário', function () {

    it('redireciona visitante não autenticado no dashboard', function () {
        $this->get('/minha-conta')->assertRedirect();
    });

    it('redireciona visitante não autenticado no histórico', function () {
        $this->get('/minha-conta/historico')->assertRedirect();
    });

    it('usuário autenticado acessa o dashboard', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/minha-conta')
            ->assertSuccessful();
    });

    it('usuário autenticado acessa o histórico', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/minha-conta/historico')
            ->assertSuccessful();
    });

});

// ==========================================
// Dashboard — Últimas Visualizações
// ==========================================

describe('Dashboard com últimas visualizações', function () {

    it('mostra mensagem quando não há visualizações', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/minha-conta')
            ->assertSuccessful()
            ->assertSee('Nenhuma análise de IA visualizada ainda');
    });

    it('mostra visualizações recentes no dashboard', function () {
        $user = User::factory()->create();

        ContentView::factory()->count(3)->create([
            'user_id' => $user->id,
            'content_type' => 'tese',
            'tribunal' => 'stf',
        ]);

        $this->actingAs($user)
            ->get('/minha-conta')
            ->assertSuccessful()
            ->assertSee('STF')
            ->assertSee('Ver tudo');
    });

    it('limita a 5 visualizações no dashboard', function () {
        $user = User::factory()->create();

        ContentView::factory()->count(8)->create([
            'user_id' => $user->id,
            'content_type' => 'tese',
            'tribunal' => 'stj',
        ]);

        $this->actingAs($user)
            ->get('/minha-conta')
            ->assertSuccessful()
            ->assertSee('Ver tudo');
    });

    it('não mostra visualizações de outro usuário', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();

        ContentView::factory()->count(3)->create([
            'user_id' => $other->id,
            'content_type' => 'tese',
            'tribunal' => 'stf',
        ]);

        $this->actingAs($user)
            ->get('/minha-conta')
            ->assertSuccessful()
            ->assertSee('Nenhuma análise de IA visualizada ainda');
    });

});

// ==========================================
// Histórico Paginado
// ==========================================

describe('Histórico de visualizações paginado', function () {

    it('mostra mensagem quando não há visualizações', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/minha-conta/historico')
            ->assertSuccessful()
            ->assertSee('ainda não visualizou');
    });

    it('lista visualizações do usuário', function () {
        $user = User::factory()->create();

        ContentView::factory()->count(5)->create([
            'user_id' => $user->id,
            'content_type' => 'tese',
            'tribunal' => 'stf',
        ]);

        $this->actingAs($user)
            ->get('/minha-conta/historico')
            ->assertSuccessful()
            ->assertSee('STF');
    });

    it('pagina quando há mais de 20 visualizações', function () {
        $user = User::factory()->create();

        ContentView::factory()->count(25)->create([
            'user_id' => $user->id,
            'content_type' => 'tese',
            'tribunal' => 'stf',
        ]);

        $response = $this->actingAs($user)->get('/minha-conta/historico');
        $response->assertSuccessful();

        // Página 2 deve existir
        $this->actingAs($user)
            ->get('/minha-conta/historico?page=2')
            ->assertSuccessful();
    });

    it('não mostra visualizações de outro usuário', function () {
        $user = User::factory()->create();
        $other = User::factory()->create();

        ContentView::factory()->count(3)->create([
            'user_id' => $other->id,
            'content_type' => 'tese',
            'tribunal' => 'stf',
        ]);

        $this->actingAs($user)
            ->get('/minha-conta/historico')
            ->assertSuccessful()
            ->assertSee('ainda não visualizou');
    });

});

// ==========================================
// Navegação do Painel
// ==========================================

describe('Navegação do painel', function () {

    it('mostra links de navegação no dashboard', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/minha-conta')
            ->assertSuccessful()
            ->assertSee('Visão Geral')
            ->assertSee('Histórico')
            ->assertSee('Coleções')
            ->assertSee('em breve')
            ->assertSee('Perfil');
    });

    it('mostra links de navegação no histórico', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/minha-conta/historico')
            ->assertSuccessful()
            ->assertSee('Visão Geral')
            ->assertSee('Histórico')
            ->assertSee('Coleções')
            ->assertSee('Perfil');
    });

});
