<?php

namespace App\Filament\Pages;

use App\Constants\CacheKeys;
use App\ExternalServices\FirebaseFCM;
use App\Filament\Actions\MarkImportantActions;
use App\Models\Ticket;
use App\Services\DatabaseUserNotification;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class TodaysTickets extends Page implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected string $view = 'filament.pages.todays-tickets';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Lifebuoy;

    public function table(Table $table): Table
    {
        return $table
            ->poll()
            ->paginationMode(PaginationMode::Simple)
            ->query(
                fn(): Builder => Ticket::query()
                    ->whereBetween('created_at', now()->inApplicationTodayRange())
                    ->whereNull('processed_at')
            )
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('user.name')->label('User'),
                TextColumn::make('question')->limit(50),
                IconColumn::make('is_important')->boolean(),
                TextColumn::make('processed_at')->dateTime(),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->recordActions([
                ...MarkImportantActions::configure(),
                Action::make('process')
                    ->authorize('process')
                    ->label('Process')
                    ->icon(Heroicon::Check)
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->processed_at === null)
                    ->schema([
                        Textarea::make('reply')->required(),
                    ])
                    ->action(function (Ticket $record, array $data) {
                        $reply = $data['reply'];
                        $record->update(
                            [
                                'processed_at' => now(),
                                'reply' => $data['reply'],
                            ]
                        );
                        FirebaseFCM::sendTicketNotification($record, $reply);
                        DatabaseUserNotification::sendTicketNotification($record, $reply);
                        Notification::make()
                            ->success()
                            ->title("Ticket #{$record->id} marked as processed")
                            ->send();
                    }),
            ])
            ->toolbarActions([
                Action::make('open_full_page')
                    ->color('primary')
                    ->url(fn() => route('filament.admin.resources.tickets.index'), true),
            ]);
    }

    public static function canAccess(): bool
    {
        return auth()->user()->canViewTodayTickets();
    }

    public static function getNavigationBadge(): ?string
    {
        return Cache::remember(
            CacheKeys::TODAY_SUPPORT_COUNT,
            now()->addDay(),
            fn() => Ticket::query()
                ->whereBetween('created_at', now()->inApplicationTodayRange())
                ->whereNull('processed_at')->count()
        );
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'The number of unprocessed tickets for today';
    }
}
