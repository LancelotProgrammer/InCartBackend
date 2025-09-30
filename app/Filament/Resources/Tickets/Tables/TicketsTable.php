<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Filament\Actions\TicketAndFeedbackActions;
use App\Services\DatabaseUserNotification;
use App\Services\FirebaseFCM;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // TODO: handle scope
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('user.name')->label('User'),
                TextColumn::make('question')->limit(50),
                IconColumn::make('is_important')->boolean(),
                TextColumn::make('processed_at')->dateTime(),
                TextColumn::make('created_at')->dateTime()->toggleable(),
                TextColumn::make('updated_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->filters([
                Filter::make('is_important')
                    ->label('Important')
                    ->query(fn ($query) => $query->where('is_important', true)),
                TernaryFilter::make('processed')
                    ->label('Processed')
                    ->nullable()
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('processed_at'),
                        false: fn ($query) => $query->whereNull('processed_at'),
                        blank: fn ($query) => $query,
                    ),
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                ...TicketAndFeedbackActions::configure('Ticket'),
                DeleteAction::make(),
                ViewAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
