<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    config()->set('services.sendy.list_internal_id', 2);
    fakeSendyConnection();
});

it('atualiza cache de usuários inscritos no Sendy via DB', function () {
    $subscribed = User::factory()->create(['email' => 'inscrito@gmail.com']);
    $notListed = User::factory()->create(['email' => 'fora@gmail.com']);

    DB::connection('sendy')->table('subscribers')->insert([
        'email' => 'inscrito@gmail.com',
        'list' => 2,
        'unsubscribed' => 0,
        'bounced' => 0,
        'complaint' => 0,
        'confirmed' => 1,
    ]);

    $this->artisan('newsletter:sync', ['--all' => true])
        ->assertSuccessful();

    $subscribed->refresh();
    $notListed->refresh();

    expect($subscribed->newsletter_subscribed_at)->not->toBeNull()
        ->and($subscribed->newsletter_synced_at)->not->toBeNull()
        ->and($notListed->newsletter_subscribed_at)->toBeNull()
        ->and($notListed->newsletter_synced_at)->not->toBeNull();
});

it('opção --user sincroniza apenas um usuário', function () {
    $user = User::factory()->create(['email' => 'unico@gmail.com']);

    DB::connection('sendy')->table('subscribers')->insert([
        'email' => 'unico@gmail.com',
        'list' => 2,
        'unsubscribed' => 0,
        'bounced' => 0,
        'complaint' => 0,
        'confirmed' => 1,
    ]);

    $this->artisan('newsletter:sync', ['--user' => $user->id])
        ->assertSuccessful();

    $user->refresh();

    expect($user->newsletter_subscribed_at)->not->toBeNull();
});

it('sem --all só processa usuários com sync antigo ou nulo', function () {
    $stale = User::factory()->create([
        'email' => 'stale@gmail.com',
        'newsletter_synced_at' => now()->subHours(7),
    ]);
    $fresh = User::factory()->create([
        'email' => 'fresh@gmail.com',
        'newsletter_synced_at' => now()->subHour(),
    ]);

    DB::connection('sendy')->table('subscribers')->insert([
        ['email' => 'stale@gmail.com', 'list' => 2, 'unsubscribed' => 0, 'bounced' => 0, 'complaint' => 0, 'confirmed' => 1],
        ['email' => 'fresh@gmail.com', 'list' => 2, 'unsubscribed' => 0, 'bounced' => 0, 'complaint' => 0, 'confirmed' => 1],
    ]);

    $this->artisan('newsletter:sync')
        ->assertSuccessful();

    $stale->refresh();
    $fresh->refresh();

    expect($stale->newsletter_subscribed_at)->not->toBeNull()
        ->and($fresh->newsletter_subscribed_at)->toBeNull();
});

it('falha quando --user aponta para id inexistente', function () {
    $this->artisan('newsletter:sync', ['--user' => 999999])
        ->assertFailed();
});
