<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HawkerCenter>
 */
class HawkerCenterFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->unique()->numberBetween(1, 9999),
            'name' => fake()->company().' Hawker Centre',
            'address' => fake()->address(),
            'latitude' => fake()->latitude(1.2, 1.5),
            'longitude' => fake()->longitude(103.6, 104.0),
            'photo_url' => fake()->optional()->imageUrl(),
        ];
    }
}
