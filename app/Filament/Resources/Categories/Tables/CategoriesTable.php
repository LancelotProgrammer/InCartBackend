<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Filament\Actions\PublishActions;
use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginationPageOptions([100])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->defaultGroup('parent_id')
            ->groups([
                Group::make('parent_id')
                    ->label('Parent Category')
                    ->getTitleFromRecordUsing(fn ($record) => optional($record->parent)->title ?? 'Root')
                    ->collapsible(),
            ])
            ->columns([
                Stack::make([
                    ImageColumn::make('url')
                        ->label('Image')
                        ->state(fn ($record) => $record->files->first()->url ?? null),
                    TextColumn::make('title')->searchable(),
                    TextColumn::make('published_at')->dateTime(),
                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 4,
            ])
            ->filters([
                Filter::make('category_name')
                    ->schema([
                        TextInput::make('title'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $search = $data['title'];
                        $category = Category::where('title->en', 'like', "%{$search}%")->orWhere('title->ar', 'like', "%{$search}%")->first();
                        if ($category) {
                            return $query->when(
                                $data['title'],
                                fn (Builder $query, $date): Builder => $query->where('id', '=', $category->id)->orWhere('parent_id', '=', $category->id),
                            );
                        }

                        // NOTE: fix this temporary code: this code add a dummy where clause to the builder to indicate that there is no categories with the provided title. Then filament displays for the user (no result) message
                        return $query->where('title', '=', '123456789');
                    }),
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                Action::make('products')
                    ->visible(fn (Category $record) => $record->parent_id !== null)
                    ->icon(Heroicon::Bars4)
                    ->url(fn (Category $record) => CategoryResource::getUrl('products', ['record' => $record->id])),
                Action::make('categories')
                    ->icon(Heroicon::Bars4)
                    ->url(fn (Category $record) => CategoryResource::getUrl('categories', ['record' => $record->id])),
                ...PublishActions::configure(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
