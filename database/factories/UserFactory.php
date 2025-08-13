<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->unique()->phoneNumber(),
            'phone_verified_at' => fake()->boolean(50) ? now() : null,
            'role_id' => Role::where('title', '=', 'user')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Makkah')->value('id'),
        ];
    }
}
