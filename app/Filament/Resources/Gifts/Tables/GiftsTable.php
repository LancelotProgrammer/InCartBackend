<?php

namespace App\Filament\Resources\Gifts\Tables;

use App\Filament\Actions\PublishActions;
use App\Models\Gift;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GiftsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('points'),
                TextColumn::make('discount'),
                TextColumn::make('allowed_sub_total_price'),
                TextColumn::make('published_at')->dateTime()->placeholder('Not published'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
                ...PublishActions::configure(Gift::class),
                Action::make('show_code')
                    ->authorize('showCode')
                    ->schema([
                        TextEntry::make('code'),
                    ])
                    ->modalSubmitAction(false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
