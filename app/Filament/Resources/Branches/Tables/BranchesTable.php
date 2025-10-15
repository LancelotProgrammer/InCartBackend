<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Filament\Actions\PublishActions;
use App\Models\Branch;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title')->searchable(),
                TextColumn::make('city.name')->numeric()->sortable(),
                TextColumn::make('latitude'),
                TextColumn::make('longitude'),
                IconColumn::make('is_default')->boolean(),
                TextColumn::make('published_at')->dateTime(),
            ])
            ->groups([
                'city.id',
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('mark_as_default')
                    ->authorize('markAsDefault')
                    ->requiresConfirmation()
                    ->visible(fn (Branch $row) => $row->is_default === false)
                    ->action(function (Branch $row) {
                        $alreadyExists = $row->newQuery()
                            ->where('city_id', $row->city_id)
                            ->where('is_default', true)
                            ->where('id', '!=', $row->id)
                            ->exists();
                        if ($alreadyExists) {
                            Notification::make()
                                ->title('Default already exists')
                                ->body('A default record already exists for this city.')
                                ->warning()
                                ->send();

                            return;
                        }
                        $row->markAsDefault();
                        Notification::make()
                            ->title('Marked as default')
                            ->body('This record has been set as the default for the city.')
                            ->success()
                            ->send();
                    }),
                Action::make('unmark_as_default')
                    ->authorize('unmarkAsDefault')
                    ->requiresConfirmation()
                    ->visible(fn (Branch $row) => $row->is_default === true)
                    ->action(function (Branch $row) {
                        $row->unmarkAsDefault();
                        $cityName = $row->city->name;
                        Notification::make()
                            ->title('Unmarked as default')
                            ->body("{$cityName} currently has no default branch assigned.")
                            ->warning()
                            ->send();

                    }),
                ...PublishActions::configure(Branch::class),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
