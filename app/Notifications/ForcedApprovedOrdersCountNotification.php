<?php

namespace App\Notifications;

use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class ForcedApprovedOrdersCountNotification extends Notification
{
    public function __construct(private readonly int $count)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title("Forced approved orders count exceeded the limit")
            ->body("The system detected {$this->count} orders that were force approved in the last week.")
            ->getDatabaseMessage();
    }
}
