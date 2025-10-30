<?php

namespace App\Filament\Resources\Advertisements\Tables;

use App\Enums\AdvertisementLink;
use App\Filament\Actions\PublishActions;
use App\Filament\Filters\BranchSelectFilter;
use App\Models\Advertisement;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
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
            ->defaultSort('id', 'desc')
            ->reorderable('order')
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title')->searchable(),
                TextColumn::make('order')->sortable()->numeric(),
                TextColumn::make('type')->badge(),
                TextColumn::make('link')->badge(),
                TextColumn::make('branch.title'),
                TextColumn::make('created_at')->dateTime(),
                IconColumn::make('can_be_published')
                    ->boolean()
                    ->tooltip(fn ($record) => $record->can_not_be_published_reason),
                TextColumn::make('published_at')->dateTime()->placeholder('Not published'),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->filters([
                BranchSelectFilter::configure(),
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
                DeleteAction::make(),
                ...PublishActions::configure(Advertisement::class),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
