<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Barra de teste (metered wall / papéis)
    |--------------------------------------------------------------------------
    |
    | Em produção, defina TEST_TOOLBAR_ENABLED=true no .env apenas enquanto
    | precisar validar; depois remova ou defina false. O email autorizado
    | pode ser sobrescrito com TEST_TOOLBAR_EMAIL.
    |
    | Fora de produção, a barra fica ativa por defeito (sem variável no .env).
    |
    */
    'test_toolbar_enabled' => filter_var(
        env('TEST_TOOLBAR_ENABLED', env('APP_ENV', 'production') !== 'production'),
        FILTER_VALIDATE_BOOLEAN
    ),

    'test_toolbar_email' => env('TEST_TOOLBAR_EMAIL', 'ivanaredler@gmail.com'),

];
