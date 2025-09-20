<?php

namespace App\Filament\Resources\Branches\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponsRelationManager extends RelationManager
{
    protected static string $relationship = 'coupons';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title')->searchable(),
                TextColumn::make('start_date')->state(fn($record) => $record->config['start_date'])->dateTime(),
                TextColumn::make('end_date')->state(fn($record) => $record->config['end_date'])->dateTime(),
                TextColumn::make('published_at')->dateTime(),
            ]);
    }
}
