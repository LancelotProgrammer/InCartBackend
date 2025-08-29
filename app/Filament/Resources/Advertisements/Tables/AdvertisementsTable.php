<?php

namespace App\Filament\Resources\Advertisements\Tables;

use App\Enums\AdvertisementLink;
use App\Enums\AdvertisementType;
use App\Filament\Actions\PublishActions;
use App\Filament\Filters\BranchSelectFilter;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdvertisementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title')->searchable(),
                TextColumn::make('order')->numeric(),
                TextColumn::make('type')->badge(),
                TextColumn::make('link')->badge(),
                TextColumn::make('published_at')->dateTime(),
                TextColumn::make('created_at')->dateTime(),
                TextColumn::make('branch.title'),
            ])
            ->filters([
                BranchSelectFilter::configure(),
                SelectFilter::make('type')->options(collect(AdvertisementType::cases())->pluck('name', 'value')->toArray()),
                SelectFilter::make('link')->options(collect(AdvertisementLink::cases())->pluck('name', 'value')->toArray())
                    ->query(function (Builder $query, array $data) {
                        return match ((int) $data['value']) {
                            AdvertisementLink::PRODUCT->value => $query->whereNotNull('product_id')->whereNotNull('category_id'),
                            AdvertisementLink::CATEGORY->value => $query->whereNull('product_id')->whereNotNull('category_id'),
                            AdvertisementLink::EXTERNAL->value => $query->whereNull('product_id')->whereNull('category_id'),
                            default => $query
                        };
                    }),
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                ViewAction::make(),
                ...PublishActions::configure(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
