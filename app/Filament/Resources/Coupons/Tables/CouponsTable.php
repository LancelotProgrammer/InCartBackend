<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Filament\Actions\PublishActions;
use App\Filament\Filters\BranchSelectFilter;
use App\Models\Coupon;
use App\Traits\HandleDeleteDependencies;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CouponsTable
{
    use HandleDeleteDependencies;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('title')->searchable(),
                TextColumn::make('start_date')->state(fn($record) => $record->config['start_date'])->dateTime(),
                TextColumn::make('end_date')->state(fn($record) => $record->config['end_date'])->dateTime(),
                TextColumn::make('branch.title'),
                TextColumn::make('published_at')->dateTime(),
            ])
            ->groups([
                'branch.id',
            ])
            ->filtersTriggerAction(
                fn(Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->filters([
                BranchSelectFilter::configure(),
                TernaryFilter::make('is_active')
                    ->label('Active filter')
                    ->placeholder('All coupons')
                    ->trueLabel('Active Coupons')
                    ->falseLabel('Disabled Coupons')
                    ->queries(
                        true: fn(Builder $query) => $query
                            ->whereNotNull('published_at')
                            ->where(function ($q) {
                                $q->whereNull('config->start_date')
                                    ->orWhere('config->start_date', '<=', now());
                            })
                            ->where(function ($q) {
                                $q->whereNull('config->end_date')
                                    ->orWhere('config->end_date', '>=', now());
                            }),
                        false: fn(Builder $query) => $query->where(function ($q) {
                            $q->whereNull('published_at')
                                ->orWhere('config->start_date', '>', now())
                                ->orWhere('config->end_date', '<', now());
                        }),
                        blank: fn(Builder $query) => $query,
                    ),
            ], layout: FiltersLayout::Modal)
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make()->using(fn ($record, $action) => (new static)->deleteWithDependencyCheck()($record, $action)),
                ...PublishActions::configure(Coupon::class),
                Action::make('show_code')
                    ->authorize('showCode')
                    ->schema([
                        TextEntry::make('code'),
                    ])
                    ->modalSubmitAction(false),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
