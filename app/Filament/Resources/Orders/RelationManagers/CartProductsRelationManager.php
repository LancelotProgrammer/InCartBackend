<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CartProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'cartProducts';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'title'),
                TextInput::make('quantity')->numeric(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with(['product.files']);
            })
            ->columns([
                ImageColumn::make('url')->label('Image')->state(function ($record) {
                    return $record->product->files->first()->url;
                }),
                TextColumn::make('product.title')->searchable(),
                TextColumn::make('quantity')->searchable(),
            ])
            ->filters([
                //
            ])
            // TODO: handle price update
            ->headerActions([
                CreateAction::make()
                    ->schema(function () {
                        return [
                            Hidden::make('cart_id')->default($this->getOwnerRecord()->carts->first()->id),
                            Select::make('product_id')->relationship('product', 'title'),
                            TextInput::make('quantity')->numeric(255),
                        ];
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
