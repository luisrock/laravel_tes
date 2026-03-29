<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

describe('Google OAuth', function () {

    it('redireciona para o Google', function () {
        Socialite::fake('google');

        $this->get(route('auth.google'))
            ->assertRedirect();
    });

    it('cria novo usuário via callback do Google', function () {
        Socialite::fake('google', (new SocialiteUser)->map([
            'id' => 'google-id-123',
            'name' => 'Teste Google',
            'email' => 'teste@gmail.com',
        ]));

        $response = $this->get(route('auth.google.callback'));

        $user = User::query()->where('email', 'teste@gmail.com')->first();
        expect($user)->not->toBeNull()
            ->and($user->google_id)->toBe('google-id-123')
            ->and($user->name)->toBe('Teste Google');

        $this->assertAuthenticatedAs($user);
    });

    it('vincula Google a usuário existente com mesmo email', function () {
        $existingUser = User::factory()->create([
            'email' => 'existente@gmail.com',
            'email_verified_at' => now(),
        ]);

        Socialite::fake('google', (new SocialiteUser)->map([
            'id' => 'google-id-456',
            'name' => 'Existente Google',
            'email' => 'existente@gmail.com',
        ]));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(config('fortify.home'));

        $existingUser->refresh();
        expect($existingUser->google_id)->toBe('google-id-456');
        expect(User::query()->where('email', 'existente@gmail.com')->count())->toBe(1);

        $this->assertAuthenticatedAs($existingUser);
    });

    it('faz login com usuário que já tem google_id', function () {
        $user = User::factory()->create([
            'email' => 'ja-vinculado@gmail.com',
            'google_id' => 'google-id-789',
            'email_verified_at' => now(),
        ]);

        Socialite::fake('google', (new SocialiteUser)->map([
            'id' => 'google-id-789',
            'name' => 'Já Vinculado',
            'email' => 'ja-vinculado@gmail.com',
        ]));

        $this->get(route('auth.google.callback'))
            ->assertRedirect(config('fortify.home'));

        $this->assertAuthenticatedAs($user);
        expect(User::query()->where('google_id', 'google-id-789')->count())->toBe(1);
    });

    it('exibe botão Google na tela de login', function () {
        $this->get('/login')
            ->assertSuccessful()
            ->assertSee('Google')
            ->assertSee(route('auth.google'));
    });

    it('exibe botão Google na tela de registro', function () {
        $this->get('/register')
            ->assertSuccessful()
            ->assertSee('Google')
            ->assertSee(route('auth.google'));
    });

    it('gera nome único com sufixo quando nome do Google já existe', function () {
        User::factory()->create(['name' => 'Nome Existente']);

        Socialite::fake('google', (new SocialiteUser)->map([
            'id' => 'google-id-unique',
            'name' => 'Nome Existente',
            'email' => 'nomeunico@gmail.com',
        ]));

        $this->get(route('auth.google.callback'));

        $newUser = User::query()->where('email', 'nomeunico@gmail.com')->first();
        expect($newUser)->not->toBeNull()
            ->and($newUser->name)->toBe('Nome Existente 2');

        $this->assertAuthenticatedAs($newUser);
    });
});
