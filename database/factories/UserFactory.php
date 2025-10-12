<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

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
            'phone' => fake()->unique()->regexify('(009665|9665|\\+9665|05|5)(5|0|3|6|4|9|1|8|7)[0-9]{7}'),
            'phone_verified_at' => now(),
            'role_id' => Role::where('code', '=', Role::ROLE_CUSTOMER_CODE)->value('id'),
            'city_id' => City::inRandomOrder()->first()->id,
        ];
    }

    /**
     * State: create an email-based user
     */
    public function emailUser(): static
    {
        return $this->state(fn () => [
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
        return $this->state(fn () => [
            'email' => null,
            'password' => null,
            'phone' => fake()->unique()->regexify('(009665|9665|\\+9665|05|5)(5|0|3|6|4|9|1|8|7)[0-9]{7}'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id'),
            'phone_verified_at' => now(),
        ]);
    }
}
