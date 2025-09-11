<?php

namespace Database\Factories;

use App\Constants\CountryLatitudeLongitude;
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
            'latitude' => 21.534925, // Jeddah Location
            'longitude' => 39.20469, // Jeddah Location
            'is_default' => true,
        ];
    }
}
