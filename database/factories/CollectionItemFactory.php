<?php

namespace Database\Factories;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CollectionItem>
 */
class CollectionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'collection_id' => Collection::factory(),
            'content_type' => $this->faker->randomElement(['tese', 'sumula']),
            'content_id' => $this->faker->numberBetween(1, 1000),
            'tribunal' => $this->faker->randomElement(['stf', 'stj', 'tst', 'tnu']),
            'order' => 0,
            'notes' => null,
        ];
    }

    /**
     * Estado: item do tipo tese.
     */
    public function tese(string $tribunal = 'stf'): static
    {
        return $this->state(fn () => [
            'content_type' => 'tese',
            'tribunal' => $tribunal,
        ]);
    }

    /**
     * Estado: item do tipo súmula.
     */
    public function sumula(string $tribunal = 'stf'): static
    {
        return $this->state(fn () => [
            'content_type' => 'sumula',
            'tribunal' => $tribunal,
        ]);
    }
}
