<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Components\TranslationComponent;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Information')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TranslationComponent::configure('title'),
                        TranslationComponent::configure('description'),
                        Select::make('parent_id')
                            ->relationship('parent', 'title')
                            ->columnSpanFull()
                            ->searchable(),
                        FileUpload::make('files')
                            ->columnSpanFull()
                            ->image()
                            ->multiple()
                            ->disk('public')
                            ->directory('advertisements')
                            ->visibility('public'),
                    ]),
            ]);
    }
}
