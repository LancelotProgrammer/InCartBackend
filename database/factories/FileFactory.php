<?php

namespace Database\Factories;

use App\Enums\FileType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'test_file'.'.'.$this->faker->fileExtension(),
            'type' => 1,
            'mime' => 'image/jpeg',
            'size' => $this->faker->numberBetween(1024, 5 * 1024 * 1024), // 1KB to 5MB
            'url' => $this->faker->imageUrl(),
        ];
    }
}
