<?php

namespace Database\Factories;

use App\Models\HawkerCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Closure>
 */
class ClosureFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+30 days');
        $endDate = (clone $startDate)->modify('+3 days');

        return [
            'hawker_center_id' => HawkerCenter::factory(),
            'type' => 'cleaning',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'remarks' => null,
        ];
    }

    public function cleaning(): static
    {
        return $this->state(fn () => [
            'type' => 'cleaning',
        ]);
    }

    public function otherWorks(): static
    {
        return $this->state(fn () => [
            'type' => 'other_works',
            'remarks' => fake()->sentence(),
        ]);
    }
}
