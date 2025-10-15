<?php

namespace App\Filament\Actions;

use App\Enums\FirebaseFCMLinks;
use App\Enums\FirebaseFCMTopics;
use App\ExternalServices\FirebaseFCM;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BulkNotificationsAction
{
    public static function configure(): Action
    {
        return Action::make('send_notification')
            ->authorize('sendNotification')
            ->label('Send Notification')
            ->color('primary')
            ->icon('heroicon-o-bell')
            ->schema([
                Section::make('information')
                    ->columns(3)
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->required(),

                        Select::make('topic')
                            ->label('Send To')
                            ->options(FirebaseFCMTopics::getTopics())
                            ->required(),

                        Select::make('link')
                            ->options(FirebaseFCMLinks::class)
                            ->live(),

                        Textarea::make('body')
                            ->columnSpanFull()
                            ->label('Body')
                            ->rows(3)
                            ->required(),

                        FileUpload::make('image')
                            ->columnSpanFull()
                            ->directory('notifications')
                            ->minSize(1)
                            ->maxSize(1024)
                            ->image()
                            ->disk('public')
                            ->visibility('public'),

                        ...FirebaseFCMLinks::getLinksModelsForm()
                    ]),
            ])
            ->action(function (array $data) {
                try {
                    FirebaseFCM::sendNotificationToTopic(
                        topic: $data['topic'],
                        title: $data['title'],
                        body: $data['body'],
                        imageUrl: isset($data['image']) ? Storage::disk('public')->url($data['image']) : null,
                        deepLink: FirebaseFCMLinks::getModelDeepLink($data)
                    );
                } catch (Throwable $e) {
                    Notification::make()
                        ->title('Notification error')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                    return;
                }
                Notification::make()
                    ->title('Notification Sent')
                    ->body("Message sent to users successfully")
                    ->success()
                    ->send();
            });
    }
}
