<?php

namespace Database\Factories;

use App\Constants\CountryLongitudeLatitude;
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
            'longitude' => $this->faker->randomFloat(6, CountryLongitudeLatitude::MIN_LONGITUDE, CountryLongitudeLatitude::MAX_LONGITUDE),
            'latitude' => $this->faker->randomFloat(6, CountryLongitudeLatitude::MIN_LONGITUDE, CountryLongitudeLatitude::MAX_LATITUDE),
            'is_default' => true,
        ];
    }
}
