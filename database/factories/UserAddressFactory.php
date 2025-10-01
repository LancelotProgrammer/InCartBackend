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

        $boundary = $city->boundary;

        $bl = collect($boundary)->firstWhere('name', 'bl');
        $tr = collect($boundary)->firstWhere('name', 'tr');

        $latitude = fake()->randomFloat(6, $bl['latitude'], $tr['latitude']);
        $longitude = fake()->randomFloat(6, $bl['longitude'], $tr['longitude']);

        return [
            'title' => $this->faker->streetName(),
            'description' => $this->faker->optional()->sentence(),
            'type' => $this->faker->randomElement(UserAddressType::cases())->value,
            'phone' => fake()->unique()->phoneNumber(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'city_id' => $city->id,
        ];
    }
}
