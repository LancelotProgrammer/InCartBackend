<?php

namespace Database\Factories;

use App\Models\Category;
use Database\Seeders\ArabicSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;

class SecondLevelCategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $text = $this->translations(['en', 'ar'], [$this->faker->sentence(2), ArabicSeeder::getFakeCategoryName()]);

        return [
            'title' => $text,
            'description' => $text,
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 years', 'now'),
            'parent_id' => Category::whereNull('parent_id')->inRandomOrder()->first()?->id,
        ];
    }
}
