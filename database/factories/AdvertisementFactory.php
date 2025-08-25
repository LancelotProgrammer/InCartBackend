<?php

namespace Database\Factories;

use App\Enums\AdvertisementLink;
use App\Enums\AdvertisementType;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use Database\Seeders\ArabicSeeder;
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
        // choose between a category advertisement or product and category advertisement and external
        $link = $this->faker->randomElement([
            AdvertisementLink::PRODUCT->value,
            AdvertisementLink::CATEGORY->value,
            AdvertisementLink::EXTERNAL->value,
        ]);

        $text = $this->translations(['en', 'ar'], [$this->faker->sentence(2), ' عرض' . ArabicSeeder::getFakeProductName()]);

        return [
            'title' => $text,
            'description' => $text,
            'order' => $this->faker->numberBetween(1, 100),
            'type' => $this->faker->randomElement(AdvertisementType::cases())->value,
            'url' => $link === AdvertisementLink::EXTERNAL->value ? $this->faker->url() : null,
            'published_at' => $this->faker->dateTimeBetween('-1 year', '-10 days'),
            'product_id' => $link === AdvertisementLink::PRODUCT->value ? Product::inRandomOrder()->first()?->id : null,
            'category_id' => $link === AdvertisementLink::PRODUCT->value || AdvertisementLink::CATEGORY->value ? Category::inRandomOrder()->first()?->id : null,
            'branch_id' => Branch::inRandomOrder()->first()->id,
        ];
    }
}
