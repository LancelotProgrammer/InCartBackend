<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class RootCategoryFactory extends Factory
{
    protected $model = Category::class;

    private static $currentIndex = 0;
    private static $rootDefinitions = [
        [
            'en_title' => 'Main 1',
            'ar_title' => 'الرئيسي 1',
            'en_description' => 'First main category',
            'ar_description' => 'الفئة الرئيسية الأولى'
        ],
        [
            'en_title' => 'Main 2',
            'ar_title' => 'الرئيسي 2',
            'en_description' => 'Second main category',
            'ar_description' => 'الفئة الرئيسية الثانية'
        ],
        [
            'en_title' => 'Main 3',
            'ar_title' => 'الرئيسي 3',
            'en_description' => 'Third main category',
            'ar_description' => 'الفئة الرئيسية الثالثة'
        ]
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $root = self::$rootDefinitions[self::$currentIndex % count(self::$rootDefinitions)];
        self::$currentIndex++;

        return [
            'title' => $this->translations(
                ['en', 'ar'],
                [$root['en_title'], $root['ar_title']]
            ),
            'description' => $this->translations(
                ['en', 'ar'],
                [$root['en_description'], $root['ar_description']]
            ),
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 years', 'now'),
            'parent_id' => null,
        ];
    }
}
