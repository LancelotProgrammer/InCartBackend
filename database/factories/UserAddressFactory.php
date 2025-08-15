<?php

namespace Database\Factories;

use App\Constants\CountryLongitudeLatitude;
use App\Models\Branch;
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
        $branchId = Branch::where('city_id', '=', $cityId)->inRandomOrder()->first()->id;

        return [
            'title' => $this->faker->streetName(),
            'description' => $this->faker->optional()->sentence(),
            'longitude' => $this->faker->randomFloat(6, CountryLongitudeLatitude::MIN_LONGITUDE, CountryLongitudeLatitude::MAX_LONGITUDE),
            'latitude' => $this->faker->randomFloat(6, CountryLongitudeLatitude::MIN_LONGITUDE, CountryLongitudeLatitude::MAX_LATITUDE),
            'city_id' => $cityId,
            'branch_id' => $branchId,
        ];
    }
}
