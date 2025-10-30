<?php

namespace App\Filament\Resources\Branches\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdvertisementsRelationManager extends RelationManager
{
    protected static string $relationship = 'advertisements';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title')->searchable(),
                TextColumn::make('order')->sortable()->numeric(),
                TextColumn::make('type')->badge(),
                TextColumn::make('link')->badge(),
                TextColumn::make('created_at')->dateTime(),
                TextColumn::make('published_at')->dateTime()->placeholder('Not published'),
            ]);
    }
}
