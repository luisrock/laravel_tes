<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        if (! $this->usesMysqlIntegrationDatabase()) {
            $this->forceSqliteInMemoryConfiguration();
        }

        parent::setUp();

        Config::set('subscription.enabled', true);
    }

    /**
     * A suíte tests/MySQL altera DB_*; forçamos SQLite em memória para o resto,
     * para que o estado não “vaze” entre classes de teste no mesmo processo.
     */
    protected function forceSqliteInMemoryConfiguration(): void
    {
        foreach ([
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
        ] as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }
    }

    protected function usesMysqlIntegrationDatabase(): bool
    {
        return is_subclass_of(static::class, MySQLTestCase::class);
    }
}
