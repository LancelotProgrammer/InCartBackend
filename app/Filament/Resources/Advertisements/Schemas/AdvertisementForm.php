<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use App\Enums\AdvertisementLink;
use App\Enums\AdvertisementType;
use App\Filament\Components\TranslationComponent;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
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
                                $set('category_id', null);
                                $set('product_id', null);
                                $set('url', null);
                                $set('file', null);
                            })
                            ->live()
                            ->required(),
                        Select::make('link')
                            ->afterStateUpdated(function (Set $set) {
                                $set('category_id', null);
                                $set('product_id', null);
                                $set('url', null);
                            })
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
                                        Select::make('category_id')
                                            ->relationship('category', 'title')
                                            ->searchable()
                                            ->getSearchResultsUsing(fn (string $search): array => Category::query()
                                                ->whereRaw('LOWER(title) LIKE ?', ['%'.strtolower($search).'%'])
                                                ->limit(50)
                                                ->pluck('title', 'id')
                                                ->all())
                                            ->getOptionLabelUsing(fn ($value): ?string => Category::find($value)?->title),
                                    ],
                                    AdvertisementLink::PRODUCT->value => [
                                        Select::make('product_id')
                                            ->relationship('product', 'title')
                                            ->searchable()
                                            ->getSearchResultsUsing(fn (string $search): array => Product::query()
                                                ->whereRaw('LOWER(title) LIKE ?', ['%'.strtolower($search).'%'])
                                                ->limit(50)
                                                ->pluck('title', 'id')
                                                ->all())
                                            ->getOptionLabelUsing(fn ($value): ?string => Product::find($value)?->title),
                                        Select::make('category_id')
                                            ->relationship('category', 'title')
                                            ->searchable()
                                            ->getSearchResultsUsing(fn (string $search): array => Category::query()
                                                ->whereRaw('LOWER(title) LIKE ?', ['%'.strtolower($search).'%'])
                                                ->limit(50)
                                                ->pluck('title', 'id')
                                                ->all())
                                            ->getOptionLabelUsing(fn ($value): ?string => Category::find($value)?->title),
                                    ],
                                    AdvertisementLink::EXTERNAL->value => [
                                        TextInput::make('url')->url(),
                                    ],
                                    default => ['pleas select a link'],
                                };
                            }),
                    ]),
                Section::make('Files')
                    ->columnSpanFull()
                    ->hidden(function (Get $get) {
                        return $get('type') === AdvertisementType::OFFER;
                    })
                    ->schema([
                        FileUpload::make('files')
                            ->columnSpanFull()
                            ->directory('advertisements')
                            ->minSize(1)
                            ->maxSize(1024)
                            ->minFiles(1)
                            ->maxFiles(5)
                            ->image()
                            ->multiple()
                            ->disk('public')
                            ->visibility('public')
                            ->required(),
                    ]),
                Section::make('Preview')
                    ->columnSpanFull()
                    ->afterHeader([
                        Action::make('refresh'),
                    ])
                    ->columns(function (Get $get) {
                        return match ($get('type')) {
                            AdvertisementType::STATUS => 2,
                            AdvertisementType::VIDEO => 1,
                            AdvertisementType::OFFER => 1,
                            AdvertisementType::CARD => 1,
                            default => 1,
                        };
                    })
                    ->schema(function (Get $get) {
                        return match ($get('type')) {
                            AdvertisementType::STATUS => [
                                View::make('advertisement-preview.status-circle'),
                                View::make('advertisement-preview.status'),
                            ],
                            AdvertisementType::VIDEO => [View::make('advertisement-preview.video')],
                            AdvertisementType::OFFER => [View::make('advertisement-preview.offer')],
                            AdvertisementType::CARD => [View::make('advertisement-preview.card')],
                            default => ['pleas select a type'],
                        };
                    }),
            ]);
    }
}
