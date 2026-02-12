<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Stripe\StripeClient;

class StripeService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = Cashier::stripe();
    }

    /**
     * Retorna todos os produtos ativos do Stripe.
     */
    public function getActiveProducts(): Collection
    {
        return Cache::remember('stripe_products', 3600, function (): Collection {
            $products = $this->stripe->products->all([
                'active' => true,
                'limit' => 100,
            ]);

            return collect($products->data);
        });
    }

    /**
     * Retorna os precos ativos de um produto.
     */
    public function getPricesForProduct(string $productId): Collection
    {
        return Cache::remember("stripe_prices_{$productId}", 3600, function () use ($productId): Collection {
            $prices = $this->stripe->prices->all([
                'product' => $productId,
                'active' => true,
                'limit' => 100,
            ]);

            return collect($prices->data);
        });
    }

    /**
     * Retorna planos formatados para exibicao na pagina de planos.
     *
     * @return array<string, array{product_id: string, name: string, description: ?string, prices: array}>
     */
    public function getFormattedPlans(): array
    {
        return Cache::remember('stripe_formatted_plans', 3600, function (): array {
            $tierProductIds = config('subscription.tier_product_ids', []);
            $plans = [];

            foreach ($tierProductIds as $productId) {
                if (empty($productId)) {
                    continue;
                }

                try {
                    $product = $this->stripe->products->retrieve($productId);
                    $prices = $this->getPricesForProduct($productId);

                    $formattedPrices = [];
                    foreach ($prices as $price) {
                        $interval = $price->recurring?->interval ?? 'one_time';
                        $key = match ($interval) {
                            'month' => 'monthly',
                            'year' => 'yearly',
                            default => $interval,
                        };
                        $formattedPrices[$key] = [
                            'id' => $price->id,
                            'amount' => $price->unit_amount / 100,
                            'currency' => strtoupper($price->currency),
                            'interval' => $interval,
                        ];
                    }

                    $tier = $product->metadata['tier'] ?? strtolower($product->name);

                    $plans[$tier] = [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'prices' => $formattedPrices,
                    ];
                } catch (Exception $e) {
                    Log::error("Erro ao buscar produto Stripe: {$productId}", [
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return $plans;
        });
    }

    /**
     * Retorna lista de price IDs validos para checkout.
     *
     * @return array<int, string>
     */
    public function getAllowedPriceIds(): array
    {
        return Cache::remember('stripe_allowed_price_ids', 3600, function (): array {
            $tierProductIds = config('subscription.tier_product_ids', []);
            $allowedIds = [];

            foreach ($tierProductIds as $productId) {
                if (empty($productId)) {
                    continue;
                }

                $prices = $this->getPricesForProduct($productId);
                foreach ($prices as $price) {
                    $allowedIds[] = $price->id;
                }
            }

            return $allowedIds;
        });
    }

    /**
     * Valida se um price ID e valido para checkout.
     */
    public function isValidPriceId(string $priceId): bool
    {
        return in_array($priceId, $this->getAllowedPriceIds());
    }

    /**
     * Limpa cache de planos (util apos alteracoes no Stripe).
     */
    public function clearCache(): void
    {
        Cache::forget('stripe_products');
        Cache::forget('stripe_formatted_plans');
        Cache::forget('stripe_allowed_price_ids');

        $tierProductIds = config('subscription.tier_product_ids', []);
        foreach ($tierProductIds as $productId) {
            if (! empty($productId)) {
                Cache::forget("stripe_prices_{$productId}");
            }
        }
    }
}
