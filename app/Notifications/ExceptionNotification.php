<?php

namespace App\Notifications;

use App\Models\Role;
use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class ExceptionNotification extends Notification
{
    public function __construct(private readonly string $title, private readonly string $body)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(User $notifiable): array
    {
        if ($notifiable->role->code !== Role::ROLE_DEVELOPER_CODE) {
            return [];
        }

        return FilamentNotification::make()
            ->title($this->title)
            ->body($this->body)
            ->danger()
            ->getDatabaseMessage();
    }
}
