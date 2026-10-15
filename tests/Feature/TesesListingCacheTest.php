<?php

use App\Models\User;
use App\Services\SearchCacheManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

/**
 * Cache de consulta das listagens completas /teses/{tribunal} (proteção contra scrapers).
 * Só o resultado da consulta é cacheado; a view continua renderizando por request.
 */
beforeEach(function () {
    /** Tabelas legadas não são migradas em SQLite; esquema mínimo para as listagens. */
    $columns = [
        'stf_teses' => ['tema_texto', 'tese_texto', 'situacao', 'relator', 'aprovadaEm', 'acordao', 'link'],
        'stj_teses' => ['orgao', 'tema', 'tese_texto', 'ramos', 'atualizadaEm', 'situacao'],
        'tst_teses' => ['titulo', 'tema', 'texto', 'tipo', 'link'],
        'tnu_teses' => ['titulo', 'ramo', 'tema', 'tese', 'relator', 'processo', 'situacao', 'link', 'julgadoEm', 'publicadoEm'],
    ];

    foreach ($columns as $table => $fields) {
        Schema::create($table, function (Blueprint $blueprint) use ($fields) {
            $blueprint->id();
            $blueprint->unsignedInteger('numero');
            foreach ($fields as $field) {
                $blueprint->text($field)->nullable();
            }
        });
    }
});

dataset('listagens', [
    'STF' => ['/teses/stf', 'stf_teses', fn (int $numero): array => ['numero' => $numero, 'tema_texto' => "{$numero} - Tema STF {$numero}", 'tese_texto' => "Tese STF {$numero}"]],
    'STJ' => ['/teses/stj', 'stj_teses', fn (int $numero): array => ['numero' => $numero, 'tema' => "Tema STJ {$numero}", 'tese_texto' => "Tese STJ {$numero}"]],
    'TST' => ['/teses/tst', 'tst_teses', fn (int $numero): array => ['numero' => $numero, 'tema' => "Tema TST {$numero}", 'texto' => "Tese TST {$numero}"]],
    'TNU' => ['/teses/tnu', 'tnu_teses', fn (int $numero): array => ['numero' => $numero, 'tema' => "Tema TNU {$numero}", 'tese' => "Tese TNU {$numero}"]],
]);

it('renderiza a listagem e serve a consulta do cache nas requisições seguintes', function (string $uri, string $table, Closure $row) {
    DB::table($table)->insert($row(101));

    $this->get($uri)->assertSuccessful()->assertSee('Tese '.strtoupper(strtok($table, '_')).' 101');

    DB::table($table)->insert($row(202));

    $this->get($uri)
        ->assertSuccessful()
        ->assertSee('Tese '.strtoupper(strtok($table, '_')).' 101')
        ->assertDontSee('Tese '.strtoupper(strtok($table, '_')).' 202');
})->with('listagens');

it('funciona com o driver file (sem tags), como em produção', function () {
    $path = storage_path('framework/testing/listing-cache-'.uniqid());
    config(['cache.stores.file.path' => $path]);
    Cache::setDefaultDriver('file');

    DB::table('stf_teses')->insert(['numero' => 101, 'tema_texto' => '101 - Tema', 'tese_texto' => 'Tese STF 101']);
    $this->get('/teses/stf')->assertSuccessful()->assertSee('Tese STF 101');

    expect(Cache::has(app(SearchCacheManager::class)->listingCacheKey('stf_teses')))->toBeTrue();

    DB::table('stf_teses')->insert(['numero' => 202, 'tema_texto' => '202 - Tema', 'tese_texto' => 'Tese STF 202']);
    $this->get('/teses/stf')->assertSuccessful()->assertDontSee('Tese STF 202');

    $this->artisan('cache:clear-searches')->assertSuccessful();
    $this->get('/teses/stf')->assertSuccessful()->assertSee('Tese STF 202');

    File::deleteDirectory($path);
});

it('mostra dados novos após limpar o cache de buscas', function (string $uri, string $table, Closure $row) {
    DB::table($table)->insert($row(101));
    $this->get($uri)->assertSuccessful();

    DB::table($table)->insert($row(202));
    $this->artisan('cache:clear-searches')->assertSuccessful();

    $this->get($uri)->assertSuccessful()->assertSee('Tese '.strtoupper(strtok($table, '_')).' 202');
})->with('listagens');

it('renderiza o token CSRF da sessão atual mesmo com a consulta em cache', function () {
    DB::table('stf_teses')->insert(['numero' => 1, 'tema_texto' => '1 - Tema', 'tese_texto' => 'Tese']);

    $this->get('/teses/stf')->assertSuccessful();

    $this->flushSession();
    $response = $this->get('/teses/stf')->assertSuccessful();

    $response->assertSee('data-csrf="'.session()->token().'"', false);
});

it('admin consulta ao vivo, sem cache, nas listagens STF e STJ', function (string $uri, string $table, Closure $row) {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    DB::table($table)->insert($row(101));
    $this->get($uri)->assertSuccessful();

    DB::table($table)->insert($row(202));

    $this->actingAs($admin)->get($uri)->assertSuccessful()->assertSee('Tese '.strtoupper(strtok($table, '_')).' 202');
})->with([
    'STF' => ['/teses/stf', 'stf_teses', fn (int $numero): array => ['numero' => $numero, 'tema_texto' => "{$numero} - Tema", 'tese_texto' => "Tese STF {$numero}"]],
    'STJ' => ['/teses/stj', 'stj_teses', fn (int $numero): array => ['numero' => $numero, 'tema' => 'Tema', 'tese_texto' => "Tese STJ {$numero}"]],
]);

it('cacheia a listagem por uma hora', function () {
    $ttl = (new ReflectionClassConstant(SearchCacheManager::class, 'LISTING_TTL_SECONDS'))->getValue();

    expect($ttl)->toBe(3600);
});

it('executa a consulta direto quando o cache falha', function () {
    Cache::shouldReceive('getStore')->andThrow(new RuntimeException('store broke'));
    Log::shouldReceive('warning')
        ->once()
        ->with(Mockery::on(fn (string $msg): bool => str_contains($msg, 'Cache de listagem falhou')));

    $result = app(SearchCacheManager::class)->rememberListing('stf', 'stf_teses', fn () => collect(['ok']));

    expect($result->all())->toBe(['ok']);
});
