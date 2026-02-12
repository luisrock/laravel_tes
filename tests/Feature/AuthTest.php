<?php

use App\Models\User;

// ==========================================
// Testes de Autenticação
// ==========================================

describe('Login', function () {

    it('exibe o formulário de login', function () {
        $this->get('/login')
            ->assertStatus(200)
            ->assertSee('Entrar');
    });

    it('permite login com credenciais válidas', function () {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    });

    it('rejeita login com senha incorreta', function () {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    });

    it('rejeita login com email inexistente', function () {
        $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
    });

    it('valida campos obrigatórios no login', function () {
        $this->post('/login', [])
            ->assertSessionHasErrors(['email', 'password']);
    });

});

describe('Logout', function () {

    it('permite logout de usuário autenticado', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    });

});

describe('Reset de Senha', function () {

    it('exibe o formulário de reset', function () {
        $this->get('/password/reset')
            ->assertStatus(200);
    });

    it('envia email de reset para email válido', function () {
        $user = User::factory()->create();

        $this->post('/password/email', [
            'email' => $user->email,
        ])->assertSessionHasNoErrors();
    });

});

describe('Proteção de Rotas', function () {

    it('redireciona usuário não autenticado para login na área de assinatura', function () {
        $this->get('/minha-conta/assinatura')
            ->assertRedirect('/login');
    });

    it('redireciona usuário não autenticado para login na área de estorno', function () {
        $this->get('/minha-conta/estorno')
            ->assertRedirect('/login');
    });

    it('bloqueia usuário comum no painel admin', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/painel')
            ->assertForbidden();
    });

});
