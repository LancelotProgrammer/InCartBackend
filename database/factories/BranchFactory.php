<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->translations('en', $this->faker->sentence(3)),
            'description' => $this->translations('en', $this->faker->optional()->paragraph()),
            'longitude' => $this->faker->randomFloat(6, 34.0, 56.5),  // Saudi Arabia longitude range
            'latitude' => $this->faker->randomFloat(6, 16.0, 32.0),   // Saudi Arabia latitude range
        ];
    }
}
