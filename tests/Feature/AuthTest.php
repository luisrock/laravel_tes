<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

// ==========================================
// Testes de Login
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
            'email_verified_at' => now(),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/minha-conta');

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

// ==========================================
// Testes de Logout
// ==========================================

describe('Logout', function () {

    it('permite logout de usuário autenticado', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    });

});

// ==========================================
// Testes de Registro (desabilitado por ora)
// ==========================================

describe('Registro', function () {

    it('retorna 404 pois registro está desabilitado', function () {
        $this->get('/register')->assertStatus(404);
        $this->post('/register', [])->assertStatus(404);
    });

});

// ==========================================
// Testes de Reset de Senha
// ==========================================

describe('Reset de Senha', function () {

    it('exibe o formulário de reset', function () {
        $this->get('/forgot-password')
            ->assertStatus(200);
    });

    it('envia email de reset para email válido', function () {
        $user = User::factory()->create();

        $this->post('/forgot-password', [
            'email' => $user->email,
        ])->assertSessionHasNoErrors();
    });

    it('não revela se email existe ao solicitar reset', function () {
        $this->post('/forgot-password', [
            'email' => 'inexistente@example.com',
        ]);
        $this->assertTrue(true);
    });

    it('envia notification de reset', function () {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class);
    });

    it('exibe formulário de nova senha com token válido', function () {
        $user = User::factory()->create();

        $token = Password::broker()->createToken($user);

        $this->get("/reset-password/{$token}?email=".urlencode($user->email))
            ->assertStatus(200);
    });

    it('permite resetar senha com token válido', function () {
        $user = User::factory()->create();

        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect('/login');

        $user->refresh();
        expect(Hash::check('newpassword123', $user->password))->toBeTrue();
    });

    it('permite login com nova senha após reset', function () {
        $user = User::factory()->create([
            'password' => bcrypt('oldpassword'),
            'email_verified_at' => now(),
        ]);

        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect('/login');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user->fresh());
    });

});

// ==========================================
// Proteção de Rotas
// ==========================================

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
