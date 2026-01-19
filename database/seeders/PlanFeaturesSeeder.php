<?php

namespace Database\Seeders;

use App\Models\PlanFeature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class PlanFeaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $featureKey = config('subscription.features.no_ads', 'no_ads');
        $productIds = array_values(array_filter(config('subscription.tier_product_ids', [])));

        if (empty($productIds)) {
            Log::warning('PlanFeaturesSeeder: tier_product_ids vazio; seed ignorado.');
            return;
        }

        foreach ($productIds as $productId) {
            PlanFeature::updateOrCreate(
                [
                    'stripe_product_id' => $productId,
                    'feature_key' => $featureKey,
                ],
                [
                    'feature_value' => null,
                ]
            );
        }
    }
}
