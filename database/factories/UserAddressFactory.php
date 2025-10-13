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
            'phone' => fake()->unique()->regexify('(009665|9665|\\+9665|05|5)(5|0|3|6|4|9|1|8|7)[0-9]{7}'),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'city_id' => $city->id,
        ];
    }
}
