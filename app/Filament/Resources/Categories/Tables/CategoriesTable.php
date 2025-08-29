<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Filament\Actions\PublishActions;
use App\Models\Category;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->with('parent.parent.parent');
            })
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('depth')->label('level'),
                TextColumn::make('title'),
                TextColumn::make('parent.title')->label('parent'),
                TextColumn::make('published_at')->dateTime(),
                TextColumn::make('created_at')->dateTime(),
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

                        // TODO fix this temporary code: this code add dummy where clause to the builder to indicate that there is no categories with the provided title. Then filament displays for the user (no result) message
                        return $query->where('title', '=', '123456789');
                    }),
            ])
            ->recordActions([
                ...PublishActions::configure(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
