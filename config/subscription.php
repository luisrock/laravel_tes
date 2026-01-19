<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe Product IDs para Tiers de Assinatura
    |--------------------------------------------------------------------------
    |
    | IDs dos produtos que representam tiers de assinatura.
    | Usado em hasFeature() para identificar o item correto da subscription.
    | OBRIGATÓRIO: Definir em .env
    |
    */
    'tier_product_ids' => array_filter([
        env('STRIPE_PRODUCT_PRO'),
        env('STRIPE_PRODUCT_PREMIUM'),
    ]),

    /*
    |--------------------------------------------------------------------------
    | Labels dos Tiers (para UI/Filament)
    |--------------------------------------------------------------------------
    */
    'tier_labels' => array_filter(
        [
            env('STRIPE_PRODUCT_PRO') => 'PRO',
            env('STRIPE_PRODUCT_PREMIUM') => 'PREMIUM',
        ],
        fn ($value, $key) => !empty($key) && !empty($value),
        ARRAY_FILTER_USE_BOTH
    ),

    /*
    |--------------------------------------------------------------------------
    | Feature Keys
    |--------------------------------------------------------------------------
    |
    | Constantes para evitar typos em verificações de features.
    |
    */
    'features' => [
        'no_ads' => 'no_ads',
        'exclusive_content' => 'exclusive_content',
        'ai_tools' => 'ai_tools', // futuro
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Names
    |--------------------------------------------------------------------------
    |
    | Nome padrão da subscription no Cashier.
    |
    */
    'default_subscription_name' => 'default',
];
