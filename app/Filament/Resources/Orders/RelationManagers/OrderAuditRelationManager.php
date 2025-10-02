<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderAuditRelationManager extends RelationManager
{
    protected static string $relationship = 'auditsLogs';

    protected static ?string $title = 'Audit Trail';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Changed By')
                    ->default('System')
                    ->searchable(),
                TextColumn::make('event')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('old_values')
                    ->label('Old Values')
                    ->formatStateUsing(fn ($state) => json_encode($state)),
                TextColumn::make('new_values')
                    ->label('New Values')
                    ->formatStateUsing(fn ($state) => json_encode($state)),
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function canAccess(): bool
    {
        return false; // TODO: Handle authorization. This function is not working. We need to find another way to restrict access to this relation manager.
    }
}
