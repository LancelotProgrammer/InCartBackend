<?php

namespace Database\Factories;

use App\Models\Category;
use Database\Seeders\ArabicSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThirdLevelCategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // get the second level categories
        $parent = Category::whereNotNull('parent_id')->whereHas('parent', function ($q) {
            $q->whereNull('parent_id');
        })->inRandomOrder()->first();

        $text = $this->translations(['en', 'ar'], [$this->faker->sentence(2), ArabicSeeder::getFakeCategoryName()]);

        return [
            'title' => $text,
            'description' => $text,
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 years', 'now'),
            'parent_id' => $parent?->id,
        ];
    }
}
