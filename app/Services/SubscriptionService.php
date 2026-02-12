<?php

namespace App\Services;

use App\Models\PlanFeature;
use App\Models\User;

class SubscriptionService
{
    public function __construct(
        protected StripeService $stripeService,
    ) {}

    /**
     * Retorna todas as features do usuario baseado em sua assinatura.
     *
     * @return array<int, string>
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
     *
     * @return array{products_without_features: array<int, string>, features_with_invalid_product: array<int, string>}
     */
    public function validatePlanFeaturesIntegrity(): array
    {
        $tierProductIds = config('subscription.tier_product_ids', []);
        $issues = [
            'products_without_features' => [],
            'features_with_invalid_product' => [],
        ];

        foreach ($tierProductIds as $productId) {
            if (empty($productId)) {
                continue;
            }

            $featureCount = PlanFeature::forProduct($productId)->count();
            if ($featureCount === 0) {
                $issues['products_without_features'][] = $productId;
            }
        }

        $featuresProductIds = PlanFeature::pluck('stripe_product_id')->unique();
        foreach ($featuresProductIds as $productId) {
            if (! in_array($productId, $tierProductIds)) {
                $issues['features_with_invalid_product'][] = $productId;
            }
        }

        return $issues;
    }

    /**
     * Seed de features para um produto (util para setup inicial).
     *
     * @param  array<int, string>  $featureKeys
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
