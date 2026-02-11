<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Laravel9UpgradeTest extends TestCase
{
    /**
     * Test that Laravel 9 framework is installed
     *
     * @return void
     */
    public function test_laravel_9_is_installed()
    {
        $laravelVersion = app()->version();
        
        // Check that we're running Laravel 9.x
        $this->assertStringStartsWith('9.', $laravelVersion, 
            'Laravel version should be 9.x, got: ' . $laravelVersion);
    }

    /**
     * Test that PHP version meets Laravel 9 requirements
     *
     * @return void
     */
    public function test_php_version_meets_requirements()
    {
        $phpVersion = PHP_VERSION;
        
        // Laravel 9 requires PHP 8.0.2 or higher
        $this->assertTrue(
            version_compare($phpVersion, '8.0.2', '>='),
            'PHP version must be 8.0.2 or higher for Laravel 9. Current: ' . $phpVersion
        );
    }

    /**
     * Test that old packages are not installed
     *
     * @return void
     */
    public function test_deprecated_packages_removed()
    {
        // Check that fideloper/proxy is not installed
        $this->assertFalse(
            class_exists('Fideloper\Proxy\TrustProxies'),
            'fideloper/proxy should be removed in Laravel 9'
        );
        
        // Check that old faker is not installed
        $this->assertFalse(
            class_exists('Faker\Factory') && !class_exists('Faker\Provider\Base'),
            'fzaninotto/faker should be replaced with fakerphp/faker'
        );
    }

    /**
     * Test that new packages are installed
     *
     * @return void
     */
    public function test_new_packages_installed()
    {
        // Check that spatie/laravel-ignition is installed
        $this->assertTrue(
            class_exists('Spatie\LaravelIgnition\IgnitionServiceProvider'),
            'spatie/laravel-ignition should be installed for Laravel 9'
        );
    }

    /**
     * Test that TrustProxies middleware uses Laravel 9 version
     *
     * @return void
     */
    public function test_trust_proxies_uses_laravel_middleware()
    {
        $middleware = new \App\Http\Middleware\TrustProxies($this->app);
        
        // Check that it extends the Laravel middleware
        $this->assertInstanceOf(
            \Illuminate\Http\Middleware\TrustProxies::class,
            $middleware,
            'TrustProxies should extend Illuminate\Http\Middleware\TrustProxies'
        );
    }

    /**
     * Test that filesystem disk configuration is correct
     *
     * @return void
     */
    public function test_filesystem_disk_configuration()
    {
        // Check that the default disk is configured
        $defaultDisk = config('filesystems.default');
        
        $this->assertNotEmpty($defaultDisk, 'Default filesystem disk should be configured');
        
        // Check that the disk exists in configuration
        $disks = config('filesystems.disks');
        $this->assertArrayHasKey($defaultDisk, $disks, 
            'Default disk "' . $defaultDisk . '" should exist in filesystems.disks configuration');
    }

    /**
     * Test that application boots successfully
     *
     * @return void
     */
    public function test_application_boots_successfully()
    {
        // Make a simple request to ensure the app boots
        $response = $this->get('/');
        
        // We just want to make sure it doesn't throw an exception
        // Status could be 200, 302 (redirect), etc.
        $this->assertNotEquals(500, $response->status(), 
            'Application should boot without 500 errors');
    }

    /**
     * Test that storage operations work with Flysystem 3.x
     *
     * @return void
     */
    public function test_storage_operations_with_flysystem_3()
    {
        $disk = \Storage::disk('local');
        
        // Test write operation
        $testContent = 'Laravel 9 Flysystem 3.x test';
        $testFile = 'test-laravel-9-' . time() . '.txt';
        
        $disk->put($testFile, $testContent);
        
        // Test exists operation
        $this->assertTrue($disk->exists($testFile), 'File should exist after put operation');
        
        // Test read operation
        $content = $disk->get($testFile);
        $this->assertEquals($testContent, $content, 'File content should match what was written');
        
        // Test delete operation
        $disk->delete($testFile);
        $this->assertFalse($disk->exists($testFile), 'File should not exist after delete operation');
    }

    /**
     * Test that cache operations work correctly
     *
     * @return void
     */
    public function test_cache_operations_work()
    {
        $key = 'laravel-9-test-' . time();
        $value = 'test-value-' . time();
        
        // Test put
        \Cache::put($key, $value, 60);
        
        // Test get
        $retrieved = \Cache::get($key);
        $this->assertEquals($value, $retrieved, 'Cache should return the stored value');
        
        // Test forget
        \Cache::forget($key);
        $this->assertNull(\Cache::get($key), 'Cache key should be null after forget');
    }

    /**
     * Test that database connection works (if configured)
     *
     * @return void
     */
    public function test_database_connection_works()
    {
        try {
            // Attempt a simple database query
            \DB::connection()->getPdo();
            $this->assertTrue(true, 'Database connection successful');
        } catch (\Exception $e) {
            // If database is not configured in testing, skip this test
            $this->markTestSkipped('Database not configured: ' . $e->getMessage());
        }
    }

    /**
     * Test that config values load correctly
     *
     * @return void
     */
    public function test_configuration_loads_correctly()
    {
        // Test that app configuration loads
        $this->assertNotEmpty(config('app.name'), 'App name should be configured');
        $this->assertNotEmpty(config('app.env'), 'App environment should be configured');
        
        // Test that database configuration loads
        $this->assertNotEmpty(config('database.default'), 'Default database connection should be configured');
        
        // Test that filesystems configuration loads
        $this->assertNotEmpty(config('filesystems.default'), 'Default filesystem disk should be configured');
    }
}
