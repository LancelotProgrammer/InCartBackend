<?php

namespace Database\Factories;

use App\Enums\UserNotificationType;
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
            'type' => $this->faker->randomElement(UserNotificationType::cases())->value,
            'config' => ['route' => 'deep_link_test'],
            'mark_as_read' => $this->faker->boolean() ? 'yes' : 'no',
            'file_id' => File::factory(),
        ];
    }
}
