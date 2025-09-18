<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 5,
            ])
            ->columns([
                Stack::make([
                    ImageColumn::make('url')->label('Image')->state(function ($record) {
                        return $record->files->first()->url;
                    }),
                    TextColumn::make('title')->searchable(),
                    TextColumn::make('unit')->badge(),
                    TextColumn::make('brand'),
                    TextColumn::make('sku'),
                ]),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
