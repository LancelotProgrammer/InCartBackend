<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use App\Enums\AdvertisementType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AdvertisementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('description'),
                TextInput::make('order')
                    ->required()
                    ->numeric(),
                Select::make('type')
                    ->options(AdvertisementType::class)
                    ->required(),
                DateTimePicker::make('published_at'),
                Select::make('branch_id')
                    ->relationship('branch', 'title')
                    ->required(),
                Select::make('product_id')
                    ->relationship('product', 'title'),
                Select::make('category_id')
                    ->relationship('category', 'title'),
            ]);
    }
}
