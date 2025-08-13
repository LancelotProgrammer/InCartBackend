<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
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

        return [
            'title' => $this->faker->words(2, true),
            'description' => $this->faker->optional()->sentence(),
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 years', 'now'),
            'parent_id' => $parent?->id,
        ];
    }
}
