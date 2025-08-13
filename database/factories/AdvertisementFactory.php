<?php

namespace Database\Factories;

use App\Enums\AdvertisementType;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Advertisement>
 */
class AdvertisementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // choose between a category advertisement or product advertisement
        $type = $this->faker->randomElement(AdvertisementType::cases());
        $linkProduct = $this->faker->boolean(50);
        $linkCategory = ! $linkProduct && $this->faker->boolean(50);

        return [
            'title' => $this->translations('en', $this->faker->sentence(3)),
            'description' => $this->translations('en', $this->faker->optional()->paragraph()),
            'order' => $this->faker->numberBetween(1, 100),
            'type' => $type->value,
            'product_id' => $linkProduct ? Product::inRandomOrder()->first()?->id : null,
            'category_id' => $linkCategory ? Category::inRandomOrder()->first()?->id : null,
            'branch_id' => Branch::inRandomOrder()->first()->id,
        ];
    }
}
