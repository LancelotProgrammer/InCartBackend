<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginationMode(PaginationMode::Simple)
            ->defaultSort('id', 'desc')
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
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->filters([
                SelectFilter::make('city')->relationship('city', 'name'),
                SelectFilter::make('role')->relationship('role', 'title'),
                TernaryFilter::make('email_verified_at')->label('Email verified')
                    ->nullable(),
                TernaryFilter::make('phone_verified_at')->label('Phone verified')
                    ->nullable(),
                TernaryFilter::make('blocked_at')->label('Blocked')
                    ->nullable(),
                TernaryFilter::make('approved_at')->label('Approved')
                    ->nullable(),
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
                Action::make('Block')
                    ->authorize('block')
                    ->color('danger')
                    ->icon(Heroicon::ExclamationTriangle)
                    ->requiresConfirmation()
                    ->visible(function (User $record) {
                        return ! $record->isBlocked();
                    })
                    ->action(function (User $record) {
                        $record->Block();
                        Notification::make()
                            ->title('User is blocked')
                            ->body('This user has been blocked and forced to logout from the system.')
                            ->success()
                            ->send();
                    }),
                Action::make('un-block')
                    ->authorize('unblock')
                    ->color('success')
                    ->icon(Heroicon::CheckCircle)
                    ->requiresConfirmation()
                    ->visible(function (User $record) {
                        return $record->isBlocked();
                    })
                    ->action(function (User $record) {
                        $record->unBlock();
                        Notification::make()
                            ->title('User is unblocked')
                            ->body('This user has been unblocked from the system.')
                            ->success()
                            ->send();
                    }),
                Action::make('approve')
                    ->authorize('approve')
                    ->color('success')
                    ->icon(Heroicon::CheckCircle)
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => ! $record->isApproved())
                    ->action(function (User $record) {
                        $record->approve();
                        Notification::make()
                            ->title('User Approved')
                            ->body('This user has been approved successfully.')
                            ->success()
                            ->send();
                    }),

                Action::make('disapprove')
                    ->authorize('disapprove')
                    ->color('danger')
                    ->icon(Heroicon::ExclamationTriangle)
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => $record->isApproved())
                    ->action(function (User $record) {
                        $record->unApprove();
                        Notification::make()
                            ->title('User Unapproved')
                            ->body('This user has been unapproved successfully.')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
