<?php

namespace Database\Factories;

use App\Enums\UnitType;
use Database\Seeders\ArabicSeeder;
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
        $text = $this->translations(['en', 'ar'], [$this->faker->sentence(2), ArabicSeeder::getFakeProductName()]);

        return [
            'title' => $text,
            'description' => $text,
            'unit' => $this->faker->randomElement(UnitType::cases()),
            'brand' => $this->faker->optional()->company(),
            'sku' => $this->faker->unique()->bothify('SKU-####-???'), // e.g. SKU-1234-ABC
        ];
    }
}
