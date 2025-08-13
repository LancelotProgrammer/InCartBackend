<?php

namespace Database\Factories;

use App\Enums\NotificationType;
use App\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserNotification>
 */
class UserNotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'body' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(NotificationType::cases())->value,
            'deep_link' => $this->faker->url(),
            'mark_as_read' => $this->faker->boolean() ? 'yes' : 'no',
            'file_id' => File::factory(),
        ];
    }
}
