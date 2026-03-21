<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContentView>
 */
class ContentViewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'content_type' => 'tese',
            'content_id' => $this->faker->numberBetween(1, 1000),
            'tribunal' => $this->faker->randomElement(['stf', 'stj', 'tst', 'tnu']),
            'viewed_at' => now(),
        ];
    }
}
