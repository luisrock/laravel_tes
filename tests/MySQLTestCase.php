<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

abstract class MySQLTestCase extends TestCase
{
    protected static bool $mysqlSchemaInitialized = false;

    protected static bool $mysqlUnavailable = false;

    /**
     * Usado nos hooks beforeEach/afterEach dos ficheiros Pest em tests/MySQL:
     * quando a DB não está acessível, o PHPUnit ainda pode executar afterEach.
     */
    public static function integrationDatabaseUnavailable(): bool
    {
        return self::$mysqlUnavailable;
    }

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('Extensão PHP pdo_mysql necessária para a suíte tests/MySQL.');
        }

        self::applyMysqlIntegrationEnvironment();

        parent::setUp();

        $this->ensureMysqlSchemaOnce();

        if (self::$mysqlUnavailable) {
            $this->markTestSkipped(
                'MySQL de testes indisponível (host/porta/credenciais). '.
                'Suba o container: docker compose up -d --wait mysql-testing — '.
                'ou veja ARQUIVOS_MD/METERED_WALL_PLAN.md / .env.example (MYSQL_TESTING_*). '.
                'Para rodar só SQLite: composer test:sqlite'
            );
        }
    }

    /**
     * Credenciais por defeito alinhadas com docker-compose.yml (serviço mysql-testing, porta 3307).
     *
     * @see https://laravel.com/docs/testing
     */
    protected static function applyMysqlIntegrationEnvironment(): void
    {
        $host = self::readEnvDefault('MYSQL_TESTING_HOST', '127.0.0.1');
        $port = self::readEnvDefault('MYSQL_TESTING_PORT', '3307');
        $database = self::readEnvDefault('MYSQL_TESTING_DATABASE', 'teses_test');
        $username = self::readEnvDefault('MYSQL_TESTING_USERNAME', 'root');
        $password = self::readEnvDefault('MYSQL_TESTING_PASSWORD', 'password');

        foreach ([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $host,
            'DB_PORT' => $port,
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $username,
            'DB_PASSWORD' => $password,
        ] as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }
    }

    protected static function readEnvDefault(string $key, string $default): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return (string) $value;
    }

    protected function ensureMysqlSchemaOnce(): void
    {
        if (self::$mysqlSchemaInitialized) {
            return;
        }

        self::$mysqlSchemaInitialized = true;

        try {
            DB::purge();
            DB::reconnect();
            DB::connection()->getPdo();
        } catch (Throwable) {
            self::$mysqlUnavailable = true;

            return;
        }

        try {
            $exitCode = Artisan::call('migrate:fresh', [
                '--force' => true,
                '--no-interaction' => true,
            ]);

            if ($exitCode !== 0) {
                self::$mysqlUnavailable = true;
            }
        } catch (Throwable) {
            self::$mysqlUnavailable = true;
        }
    }
}
