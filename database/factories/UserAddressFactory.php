<?php

namespace Database\Factories;

use App\Enums\UserAddressType;
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
        $city = City::inRandomOrder()->first();

        // Random offset for latitude/longitude (±0.02 degrees ~ ~2km)
        $latOffset = fake()->randomFloat(5, -0.02, 0.02);
        $lngOffset = fake()->randomFloat(5, -0.02, 0.02);

        return [
            'title' => $this->faker->streetName(),
            'description' => $this->faker->optional()->sentence(),
            'type' => $this->faker->randomElement(UserAddressType::cases())->value,
            'phone' => fake()->unique()->phoneNumber(),
            'latitude' => $city->latitude + $latOffset,
            'longitude' => $city->longitude + $lngOffset,
            'city_id' => $city->id,
        ];
    }
}
