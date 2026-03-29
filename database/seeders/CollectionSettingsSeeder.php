<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class CollectionSettingsSeeder extends Seeder
{
    /**
     * Valores default dos limites de coleções por tier.
     * -1 = ilimitado (usado para PREMIUM).
     *
     * @var array<string, string>
     */
    private array $defaults = [
        'collections_registered_max' => '3',
        'collections_registered_items_max' => '15',
        'collections_pro_max' => '10',
        'collections_pro_items_max' => '50',
        'collections_premium_max' => '-1',
        'collections_premium_items_max' => '-1',
    ];

    public function run(): void
    {
        foreach ($this->defaults as $key => $value) {
            SiteSetting::firstOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
