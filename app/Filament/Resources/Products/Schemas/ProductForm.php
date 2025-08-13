<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('description'),
                Textarea::make('brand')
                    ->columnSpanFull(),
                Textarea::make('sku')
                    ->label('SKU')
                    ->columnSpanFull(),
            ]);
    }
}
