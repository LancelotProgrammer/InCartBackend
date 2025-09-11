<?php

namespace Database\Factories;

use App\Constants\CountryLatitudeLongitude;
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
        $cityId = City::inRandomOrder()->first()->id;

        return [
            'title' => $this->faker->streetName(),
            'description' => $this->faker->optional()->sentence(),
            'type' => $this->faker->randomElement(UserAddressType::cases())->value,
            'phone' => fake()->unique()->phoneNumber(),
            'latitude' => $this->faker->randomFloat(6, CountryLatitudeLongitude::MIN_LATITUDE, CountryLatitudeLongitude::MAX_LATITUDE),
            'longitude' => $this->faker->randomFloat(6, CountryLatitudeLongitude::MIN_LONGITUDE, CountryLatitudeLongitude::MAX_LONGITUDE),
            'city_id' => $cityId,
        ];
    }
}
