<?php

namespace App\Filament\Resources\Feedback\Tables;

use App\Filament\Actions\MarkImportantActions;
use App\Filament\Components\SelectBranchComponent;
use App\Models\Feedback;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FeedbackTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginationMode(PaginationMode::Simple)
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')->placeholder('Deleted customer')->label('User'),
                TextColumn::make('feedback')->limit(50),
                IconColumn::make('is_important')->boolean(),
                TextColumn::make('processed_at')->dateTime()->placeholder('Not processed yet'),
                TextColumn::make('manager.name')->label('Manager')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->dateTime()->toggleable(),
                TextColumn::make('updated_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->filters([
                Filter::make('is_important')
                    ->label('Important')
                    ->query(fn($query) => $query->where('is_important', true)),
                TernaryFilter::make('processed')
                    ->label('Processed')
                    ->nullable()
                    ->queries(
                        true: fn($query) => $query->whereNotNull('processed_at'),
                        false: fn($query) => $query->whereNull('processed_at'),
                        blank: fn($query) => $query,
                    ),
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                DeleteAction::make(),
                ViewAction::make(),
                ...MarkImportantActions::configure(),
                Action::make('process')
                    ->authorize('process')
                    ->label('Process')
                    ->icon(Heroicon::Check)
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->processed_at === null)
                    ->action(function (Feedback $record) {
                        $record->update([
                            'processed_by' => auth()->user()->id,
                            'processed_at' => now()
                        ]);
                        Notification::make()
                            ->success()
                            ->title("Feedback #{$record->id} marked as processed")
                            ->send();
                    }),
                Action::make('change_branch')
                    ->authorize('changeBranch')
                    ->icon(Heroicon::BuildingOffice2)
                    ->requiresConfirmation()
                    ->form([
                        SelectBranchComponent::configure(),
                    ])
                    ->action(function (Feedback $record, array $data) {
                        $record->update([
                            'branch_id' => $data['branch_id'],
                        ]);
                        Notification::make()
                            ->success()
                            ->title("Feedback #{$record->id} branch changed")
                            ->send();
                    }),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
