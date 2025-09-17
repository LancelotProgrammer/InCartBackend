<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Unique;

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
            'phone' => '+9665' . fake()->unique()->numerify('########'),
            'phone_verified_at' => now(),
            'role_id' => Role::where('code', '=', 'user')->value('id'),
            'city_id' => City::inRandomOrder()->first()->id,
        ];
    }

    /**
     * State: create an email-based user
     */
    public function emailUser(): static
    {
        return $this->state(fn() => [
            'phone' => null,
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'), // default password
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id'),
            'email_verified_at' => null,
        ]);
    }

    /**
     * State: create a phone-based user (explicitly)
     */
    public function phoneUser(): static
    {
        return $this->state(fn() => [
            'email' => null,
            'password' => null,
            'phone' => '+9665' . fake()->unique()->numerify('########'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id'),
            'phone_verified_at' => now(),
        ]);
    }
}
