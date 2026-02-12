<?php

namespace App\Services;

use App\Models\PlanFeature;
use App\Models\User;

class SubscriptionService
{
    protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Retorna todas as features do usuário baseado em sua assinatura.
     */
    public function getUserFeatures(User $user): array
    {
        $productId = $user->getSubscriptionPlan();

        if (! $productId) {
            return [];
        }

        return PlanFeature::forProduct($productId)
            ->pluck('feature_key')
            ->toArray();
    }

    /**
     * Valida integridade entre produtos do Stripe e features configuradas.
     * Retorna produtos órfãos (sem features) ou features órfãs (produto inexistente).
     */
    public function validatePlanFeaturesIntegrity(): array
    {
        $tierProductIds = config('subscription.tier_product_ids', []);
        $issues = [
            'products_without_features' => [],
            'features_with_invalid_product' => [],
        ];

        // Verificar produtos sem features
        foreach ($tierProductIds as $productId) {
            if (empty($productId)) {
                continue;
            }

            $featureCount = PlanFeature::forProduct($productId)->count();
            if ($featureCount === 0) {
                $issues['products_without_features'][] = $productId;
            }
        }

        // Verificar features com produtos inválidos
        $featuresProductIds = PlanFeature::pluck('stripe_product_id')->unique();
        foreach ($featuresProductIds as $productId) {
            if (! in_array($productId, $tierProductIds)) {
                $issues['features_with_invalid_product'][] = $productId;
            }
        }

        return $issues;
    }

    /**
     * Seed de features para um produto (útil para setup inicial).
     */
    public function seedFeaturesForProduct(string $productId, array $featureKeys): void
    {
        foreach ($featureKeys as $key) {
            PlanFeature::firstOrCreate([
                'stripe_product_id' => $productId,
                'feature_key' => $key,
            ]);
        }
    }
}
