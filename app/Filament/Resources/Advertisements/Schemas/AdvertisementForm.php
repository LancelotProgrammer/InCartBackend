<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use App\Enums\AdvertisementLink;
use App\Enums\AdvertisementType;
use App\Filament\Components\TranslationComponent;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class AdvertisementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Information')
                    ->columns(2)
                    ->schema([
                        TranslationComponent::configure('title'),
                        TranslationComponent::configure('description'),
                        Select::make('branch_id')->relationship('branch', 'title')->required(),
                        TextInput::make('order')->required()->numeric(),
                    ]),
                Section::make('Configs')
                    ->columns(2)
                    ->schema([
                        Select::make('type')->options(AdvertisementType::class)
                            ->afterStateUpdated(function (Set $set) {
                                $set('link', null);
                            })
                            ->live()
                            ->required(),
                        Select::make('link')
                            ->options(function (Get $get) {
                                return match ($get('type')) {
                                    AdvertisementType::STATUS => [
                                        AdvertisementLink::PRODUCT->value => AdvertisementLink::PRODUCT->getLabel(),
                                        AdvertisementLink::CATEGORY->value => AdvertisementLink::CATEGORY->getLabel(),
                                        AdvertisementLink::EXTERNAL->value => AdvertisementLink::EXTERNAL->getLabel(),
                                    ],
                                    AdvertisementType::VIDEO => [
                                        AdvertisementLink::EXTERNAL->value => AdvertisementLink::EXTERNAL->getLabel(),
                                    ],
                                    AdvertisementType::OFFER => [
                                        AdvertisementLink::PRODUCT->value => AdvertisementLink::PRODUCT->getLabel(),
                                    ],
                                    AdvertisementType::CARD => [
                                        AdvertisementLink::PRODUCT->value => AdvertisementLink::PRODUCT->getLabel(),
                                        AdvertisementLink::CATEGORY->value => AdvertisementLink::CATEGORY->getLabel(),
                                        AdvertisementLink::EXTERNAL->value => AdvertisementLink::EXTERNAL->getLabel(),
                                    ],
                                    default => [],
                                };
                            })
                            ->dehydrated(false)
                            ->live()
                            ->required(),
                        Section::make('Links')
                            ->columnSpanFull()
                            ->columns(function (Get $get) {
                                return match ((int) $get('link')) {
                                    AdvertisementLink::CATEGORY->value => 1,
                                    AdvertisementLink::PRODUCT->value => 2,
                                    AdvertisementLink::EXTERNAL->value => 1,
                                    default => 1,
                                };
                            })
                            ->schema(function (Get $get) {
                                return match ((int) $get('link')) {
                                    AdvertisementLink::CATEGORY->value => [
                                        Select::make('category_id')->relationship('category', 'title'),
                                    ],
                                    AdvertisementLink::PRODUCT->value => [
                                        Select::make('product_id')->relationship('product', 'title'),
                                        Select::make('category_id')->relationship('category', 'title'),
                                    ],
                                    AdvertisementLink::EXTERNAL->value => [
                                        TextInput::make('url')->url(),
                                    ],
                                    default => ['pleas select a link'],
                                };
                            }),
                    ]),
                Section::make('Files')
                    ->hidden(function (Get $get) {
                        return (int)$get('type') === AdvertisementType::OFFER->value;
                    })
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('files')
                            ->multiple()
                            ->disk('public')
                            ->directory('advertisements')
                            ->visibility('public'),
                    ]),
            ]);
    }
}
