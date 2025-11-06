<?php

namespace App\Filament\Resources\Supports;

use App\Filament\Resources\Supports\Pages\ManageSupports;
use App\Models\Support;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SupportResource extends Resource
{
    protected static ?string $model = Support::class;

    protected static string|UnitEnum|null $navigationGroup = 'Users';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArchiveBox;

    protected static ?string $recordTitleAttribute = 'email';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('General Info')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email'),
                        TextEntry::make('message')->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                DeleteAction::make(),
                ViewAction::make(),
                Action::make('process')
                    ->authorize('process')
                    ->label('Process')
                    ->icon(Heroicon::Check)
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->processed_at === null)
                    ->action(function (Support $record) {
                        $record->update([
                            'processed_by' => auth()->user()->id,
                            'processed_at' => now(),
                        ]);
                        Notification::make()
                            ->success()
                            ->title("Support #{$record->id} marked as processed")
                            ->send();
                    }),
            ])
            ->toolbarActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSupports::route('/'),
        ];
    }
}
