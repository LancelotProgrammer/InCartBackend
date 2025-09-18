<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Filament\Actions\PublishActions;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

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
                'city.name',
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                Action::make('mark_as_default')
                    ->hidden(fn(Model $row) => $row->is_default === true)
                    ->action(function (Model $row) {
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
                        $row->is_default = true;
                        $row->save();
                        Notification::make()
                            ->title('Marked as default')
                            ->body('This record has been set as the default for the city.')
                            ->success()
                            ->send();
                    }),
                Action::make('mark_as_not_default')
                    ->hidden(fn(Model $row) => $row->is_default === false) // only show if currently default
                    ->action(function (Model $row) {
                        if (! $row->is_default) {
                            Notification::make()
                                ->title('Already not default')
                                ->body('This record is already marked as not default.')
                                ->info()
                                ->send();

                            return;
                        }
                        $row->is_default = false;
                        $row->save();
                        Notification::make()
                            ->title('Marked as not default')
                            ->body('This record is no longer the default for its city.')
                            ->success()
                            ->send();
                    }),
                ...PublishActions::configure(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
