<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Components\TranslationComponent;
use App\Models\Category;
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
                            ->disabled(function ($operation) {
                                return $operation === 'edit';
                            })
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search): array => Category::query()
                                ->whereRaw('LOWER(title) LIKE ?', ['%'.strtolower($search).'%'])
                                ->limit(50)
                                ->get()
                                ->filter(fn ($category) => $category->depth < 3)
                                ->pluck('title', 'id')
                                ->all())
                            ->getOptionLabelUsing(fn ($value): ?string => Category::find($value)?->title)
                            ->columnSpanFull(),
                        FileUpload::make('files')
                            ->visible(function ($operation) {
                                return $operation === 'create';
                            })
                            ->columnSpanFull()
                            ->directory('categories')
                            ->minSize(1)
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
