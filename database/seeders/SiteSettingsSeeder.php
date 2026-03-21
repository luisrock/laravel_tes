<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Valores default para configurações do site.
     *
     * @var array<string, string>
     */
    private array $defaults = [
        'metered_wall_enabled' => '1',
        'metered_wall_daily_limit' => '3',
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
