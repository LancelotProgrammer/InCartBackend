<?php

namespace App\Filament\Pages;

use App\Constants\CacheKeys;
use App\Filament\Actions\OrderActions;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class TodaysOrders extends Page implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected string $view = 'filament.pages.todays-orders';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ShoppingCart;

    public function table(Table $table): Table
    {
        return $table
            ->poll()
            ->paginationMode(PaginationMode::Simple)
            ->query(fn(): Builder => Order::query()->whereDate('delivery_date', '=', now()->toDateString()))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('branch.title')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('order_number')->searchable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('customer', function (Builder $q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('order_status')->badge(),
                TextColumn::make('payment_status')->badge(),
                TextColumn::make('delivery_status')->badge(),

                TextColumn::make('total_price')->money('SAR'),
                TextColumn::make('delivery_date')->dateTime(),

                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('delivery.name'),
                TextColumn::make('coupon.title')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('paymentMethod.title')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('userAddress.title')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('view')
                    ->authorize('view')
                    ->color('primary')
                    ->url(fn($record) => route('filament.admin.resources.orders.view', $record->id), true),
                Action::make('edit')
                    ->authorize('update')
                    ->color('primary')
                    ->url(fn($record) => route('filament.admin.resources.orders.edit', $record->id), true),
                Action::make('invoice')
                    ->authorize('viewInvoice')
                    ->icon(Heroicon::OutlinedArrowDownCircle)
                    ->color('primary')
                    ->url(fn(Order $record) => route('web.order.invoice', ['id' => $record->id]), true),
                OrderActions::configure(false),
            ])
            ->toolbarActions([
                Action::make('open_full_page')
                    ->color('primary')
                    ->url(fn() => route('filament.admin.resources.orders.index'), true),
            ]);
    }

    public static function canAccess(): bool
    {
        return auth()->user()->canViewTodaysOrders();
    }

    public static function getNavigationBadge(): ?string
    {
        return Cache::remember(
            CacheKeys::PENDING_ORDER_COUNT,
            now()->addDay(),
            fn() => Order::whereDate('delivery_date', now()->toDateString())->count()
        );
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'The number of pending orders for today';
    }
}
