<?php

namespace App\Filament\Resources\Branches\RelationManagers;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('user_id', 'desc')
            ->columns([
                Split::make([
                    TextColumn::make('name')
                        ->weight(FontWeight::Bold)
                        ->searchable(),
                    Stack::make([
                        TextColumn::make('email')
                            ->icon(Heroicon::Envelope)
                            ->searchable(),
                        TextColumn::make('email_verified_at')
                            ->icon(Heroicon::CheckCircle)
                            ->prefix('Email verified at: ')
                            ->date(),
                        TextColumn::make('phone')
                            ->icon(Heroicon::DevicePhoneMobile)
                            ->searchable(),
                        TextColumn::make('phone_verified_at')
                            ->icon(Heroicon::CheckCircle)
                            ->prefix('Phone verified at: ')
                            ->date(),
                    ]),
                    Stack::make([
                        TextColumn::make('blocked_at')->icon(Heroicon::ExclamationCircle)->prefix('Blocked At: ')->date(),
                        TextColumn::make('approved_at')->icon(Heroicon::Star)->prefix('Approved At: ')->date(),
                        TextColumn::make('loyalty.total_earned')->icon(Heroicon::Gift)->prefix('Total loyalty point: '),
                    ]),
                    Stack::make([
                        TextColumn::make('city.name')->icon(Heroicon::OutlinedGlobeAlt),
                        TextColumn::make('role.title')->icon(Heroicon::UserGroup),
                    ]),
                ]),
            ])->headerActions([
                Action::make('add')
                    ->schema([
                        Select::make('user_id')
                            ->options(User::where('city_id', '=', $this->ownerRecord->city_id)
                                ->getUsersWhoCanBeAssignedToBranch()
                                ->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        if (User::find($data['user_id'])->branches()->exists()) {
                            Notification::make()
                                ->title('User already attached to a branch')
                                ->warning()
                                ->send();

                            return;
                        }
                        $this->getRelationship()->attach($data['user_id']);
                        Notification::make()
                            ->title('User attached successfully')
                            ->success()
                            ->send();
                    }),
            ])->recordActions([
                Action::make('detach')
                    ->defaultColor('danger')
                    ->icon(Heroicon::XMark)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        if (! $record) {
                            Notification::make()
                                ->title('Record Conflict')
                                ->body('This record has been modified by another user. Please refresh and try again.')
                                ->warning()
                                ->send();

                            return;
                        }
                        $this->getOwnerRecord()->users()->detach($record->id);
                        Notification::make()
                            ->title('Users detached')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
