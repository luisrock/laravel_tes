<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('shows the CTA topbar for guests on the home page', function () {
    get('/')
        ->assertOk()
        ->assertSee('Criar Conta Grátis')
        ->assertSee('sem anúncios');
});

it('hides the CTA topbar for logged in users on the home page', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertDontSee('Criar Conta Grátis')
        ->assertDontSee('Melhore sua experiência de pesquisa');
});
