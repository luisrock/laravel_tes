<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Collection>
 */
class CollectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(3);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'description' => $this->faker->optional()->sentence(10),
            'slug' => Str::slug($title),
            'is_private' => false,
        ];
    }

    /**
     * Estado: coleção privada.
     */
    public function private(): static
    {
        return $this->state(fn () => ['is_private' => true]);
    }

    /**
     * Estado: coleção pública.
     */
    public function public(): static
    {
        return $this->state(fn () => ['is_private' => false]);
    }
}
