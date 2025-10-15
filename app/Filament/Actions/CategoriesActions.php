<?php

namespace App\Filament\Actions;

use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class CategoriesActions
{
    public static function configureViewProductsAction(): Action
    {
        return Action::make('products')
            ->authorize('viewProducts')
            ->visible(fn (Category $record) => $record->parent_id !== null)
            ->icon(Heroicon::Cube)
            ->url(fn (Category $record) => CategoryResource::getUrl('products', ['record' => $record->id]));
    }

    public static function configureViewCategoriesAction(): Action
    {
        return Action::make('categories')
            ->authorize('viewCategories')
            ->icon(Heroicon::NumberedList)
            ->visible(function ($record) {
                if (Category::where('id', '=', $record->id)->first()->depth === 3) {
                    return false;
                } else {
                    return true;
                }
            })
            ->url(fn (Category $record) => CategoryResource::getUrl('categories', ['record' => $record->id]));
    }
}
