<?php

/**
 * Arch Tests — Testes de Arquitetura
 *
 * Garantem que o código segue padrões de organização e boas práticas.
 * Esses testes NÃO fazem requisições HTTP — apenas verificam a estrutura do código.
 *
 * Referência: https://pestphp.com/docs/arch-testing
 */

// ==========================================
// Padrões Gerais
// ==========================================

arch('não usa funções de debug em código de produção')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsed();

arch('app não depende de testes')
    ->expect('App')
    ->not->toUse('Tests');

// ==========================================
// Models
// ==========================================

arch('models estão no namespace correto')
    ->expect('App\Models')
    ->toBeClasses();

arch('models estendem Eloquent Model ou Authenticatable')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Models\User');

// ==========================================
// Controllers
// ==========================================

arch('controllers estão no namespace correto')
    ->expect('App\Http\Controllers')
    ->toBeClasses();

arch('controllers estendem Controller base')
    ->expect('App\Http\Controllers')
    ->toExtend('App\Http\Controllers\Controller')
    ->ignoring([
        'App\Http\Controllers\Controller',
        'App\Http\Controllers\WebhookController', // estende CashierWebhookController
    ]);

arch('controllers não são usados diretamente por models')
    ->expect('App\Models')
    ->not->toUse('App\Http\Controllers');

// ==========================================
// Middleware
// ==========================================

arch('middleware está no namespace correto')
    ->expect('App\Http\Middleware')
    ->toBeClasses();

// ==========================================
// Services
// ==========================================

arch('services estão no namespace correto')
    ->expect('App\Services')
    ->toBeClasses();

// ==========================================
// Notifications
// ==========================================

arch('notifications estão no namespace correto')
    ->expect('App\Notifications')
    ->toBeClasses();

// ==========================================
// Jobs
// ==========================================

arch('jobs estão no namespace correto')
    ->expect('App\Jobs')
    ->toBeClasses();

// ==========================================
// Segurança: env() não deve ser usado fora de config/
// ==========================================

// Estes arquivos usam env() diretamente e devem ser migrados para config() futuramente:
// - App\Http\Middleware\BearerTokenMiddleware (API_TOKEN)
// - App\Http\Controllers\ConceptController (OPENAI_API_KEY)
// - App\Console\Commands\TestMatomoApi (MATOMO_TOKEN)
// - App\Console\Commands\SyncMatomoViews (MATOMO_TOKEN)
// - App\Console\Commands\TestS3Access (vários)
arch('env() não é usado fora de arquivos de configuração')
    ->expect('env')
    ->not->toBeUsed()
    ->ignoring([
        'App\Providers\AppServiceProvider',
        'App\Http\Middleware\BearerTokenMiddleware',
        'App\Http\Controllers\ConceptController',
        'App\Console\Commands',  // comandos artisan de diagnóstico
    ]);
