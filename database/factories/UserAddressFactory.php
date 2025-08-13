<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserAddress>
 */
class UserAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->streetName(),
            'description' => $this->faker->optional()->sentence(),
            'longitude' => $this->faker->randomFloat(6, 34.0, 56.5),  // Saudi Arabia longitude range
            'latitude' => $this->faker->randomFloat(6, 16.0, 32.0),   // Saudi Arabia latitude range
            'city_id' => City::inRandomOrder()->first()->id,
        ];
    }
}
