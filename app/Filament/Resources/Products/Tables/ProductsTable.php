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
            ->defaultPaginationPageOption(25)
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'lg' => 2,
                'xl' => 3,
                '2xl' => 5,
            ])
            ->columns([
                Stack::make([
                    ImageColumn::make('url')->label('Image')->state(fn ($record) => $record->files->first()->url ?? null)->imageSize(200),
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
