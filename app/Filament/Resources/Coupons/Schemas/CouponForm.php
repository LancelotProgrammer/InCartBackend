<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Enums\CouponType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('description'),
                TextInput::make('code')
                    ->required(),
                Select::make('type')
                    ->options(CouponType::class)
                    ->required(),
                TextInput::make('config')
                    ->required(),
                DateTimePicker::make('published_at'),
                Select::make('branch_id')
                    ->relationship('branch', 'title')
                    ->required(),
            ]);
    }
}
