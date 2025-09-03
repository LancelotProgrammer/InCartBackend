<?php

namespace Database\Factories;

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
        $bg = sprintf('%06X', mt_rand(0, 0xFFFFFF));

        $r = hexdec(substr($bg, 0, 2));
        $g = hexdec(substr($bg, 2, 2));
        $b = hexdec(substr($bg, 4, 2));
        $textColor = ($r * 299 + $g * 587 + $b * 114) / 1000 > 128 ? '000000' : 'FFFFFF';

        return [
            'name' => 'test_image' . '.' . 'png',
            'type' => 1,
            'mime' => 'image/jpeg',
            'size' => $this->faker->numberBetween(1024, 5 * 1024 * 1024),
            'url' => "https://dummyimage.com/640x480/{$bg}/{$textColor}.png?text=" . urlencode(fake()->productName),
        ];
    }
}
