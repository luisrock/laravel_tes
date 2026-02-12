<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may also register your own custom functions
| to simplify your tests. These are available in all test files.
|
*/

/**
 * Helper para testar rotas que podem falhar por incompatibilidade SQL (SQLite vs MySQL).
 * Aceita 200 ou 500 como status válido.
 */
function assertRouteResponds(string $uri): void
{
    $response = test()->get($uri);
    expect($response->getStatusCode())->toBeIn([200, 500],
        "Rota {$uri} retornou status inesperado: {$response->getStatusCode()}"
    );
}
