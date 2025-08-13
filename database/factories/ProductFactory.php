<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
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
            'brand' => $this->faker->optional()->company(),
            'sku' => $this->faker->unique()->bothify('SKU-####-???'), // e.g. SKU-1234-ABC
        ];
    }
}
