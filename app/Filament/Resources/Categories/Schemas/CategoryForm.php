<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Components\TranslationComponent;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\Category;

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
                            ->searchable()
                            ->getSearchResultsUsing(fn(string $search): array => Category::query()
                                ->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($search) . '%'])
                                ->limit(50)
                                ->pluck('title', 'id')
                                ->all())
                            ->getOptionLabelUsing(fn($value): ?string => Category::find($value)?->title)
                            ->columnSpanFull(),
                        FileUpload::make('file')
                            ->directory('categories')
                            ->minSize(512)
                            ->maxSize(1024)
                            ->minFiles(1)
                            ->maxFiles(1)
                            ->image()
                            ->multiple()
                            ->disk('public')
                            ->visibility('public')
                            ->required(),
                    ]),
            ]);
    }
}
