<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Habilitar assinaturas por padrão para não quebrar a suíte de testes existente
        \Illuminate\Support\Facades\Config::set('subscription.enabled', true);
    }
}
