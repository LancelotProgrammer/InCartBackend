<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Filament\Actions\CategoriesActions;
use App\Filament\Actions\PublishActions;
use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
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
            ->paginationPageOptions([50])
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
                    ImageColumn::make('url')->label('Image')->state(fn ($record) => $record->files->first()->url ?? null)->imageSize(200),
                    TextColumn::make('title')->searchable(),
                    TextColumn::make('published_at')->dateTime(),
                ]),
            ])
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'lg' => 2,
                'xl' => 3,
                '2xl' => 5,
            ])
            ->filters([
                Filter::make('category_name')
                    ->schema([
                        TextInput::make('title'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $search = $data['title'];
                        $category = Category::whereRaw('LOWER(title) LIKE ?', ['%'.strtolower($search).'%'])->first();
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
                CategoriesActions::configureViewProductsAction()->iconButton(),
                CategoriesActions::configureViewCategoriesAction()->iconButton(),
                ...PublishActions::configure(Category::class),
                EditAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
