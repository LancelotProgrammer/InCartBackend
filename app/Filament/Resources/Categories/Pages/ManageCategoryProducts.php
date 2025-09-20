<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ManageCategoryProducts extends ManageRelatedRecords
{
    protected static string $resource = CategoryResource::class;

    protected static string $relationship = 'products';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->contentGrid([
                'md' => 2,
                'xl' => 5,
            ])
            ->recordActions([
                Action::make('edit')
                    ->url(fn(Product $record) => ProductResource::getUrl('edit', ['record' => $record->id])),
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
            ]);
    }
}
