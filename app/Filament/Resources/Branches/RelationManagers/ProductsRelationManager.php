<?php

namespace App\Filament\Resources\Branches\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('unit')->badge(),
                TextColumn::make('price'),
                TextColumn::make('discount'),
                TextColumn::make('maximum_order_quantity'),
                TextColumn::make('minimum_order_quantity'),
                TextColumn::make('quantity'),
                TextColumn::make('expires_at'),
                TextColumn::make('published_at')->placeholder('Not published'),
            ]);
    }
}
