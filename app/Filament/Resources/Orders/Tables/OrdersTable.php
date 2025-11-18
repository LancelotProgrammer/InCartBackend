<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\DeliveryScheduledType;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Actions\OrderActions;
use App\Filament\Filters\BranchSelectFilter;
use App\Models\Order;
use App\Models\Role;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginationMode(PaginationMode::Simple)
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('branch.title')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('order_number')->searchable(),
                TextColumn::make('customer.name')->placeholder('Deleted customer')
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
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->filters([
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until')->after('created_from'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                SelectFilter::make('order_status')->options(OrderStatus::class),
                SelectFilter::make('payment_status')->options(PaymentStatus::class),
                SelectFilter::make('delivery_status')->options(DeliveryStatus::class),
                SelectFilter::make('delivery_scheduled_type')->options(DeliveryScheduledType::class),
                SelectFilter::make('customer')
                    ->relationship(
                        'customer',
                        'name',
                    )->searchable(),
                SelectFilter::make('manager')
                    ->relationship(
                        'manager',
                        'name',
                    )->searchable(),
                SelectFilter::make('delivery')
                    ->relationship(
                        'delivery',
                        'name',
                    )->searchable(),
                BranchSelectFilter::configure(),
            ], FiltersLayout::Modal)
            ->filtersFormColumns(3)
            ->filtersFormSchema(function (array $filters) {
                return [
                    Section::make('Date')
                        ->columnSpanFull()
                        ->columns(1)
                        ->schema([
                            $filters['created_at'],
                        ]),
                    Section::make('Status')
                        ->columnSpanFull()
                        ->columns(4)
                        ->schema([
                            $filters['order_status'],
                            $filters['payment_status'],
                            $filters['delivery_status'],
                            $filters['delivery_scheduled_type'],
                        ]),
                    Section::make('User')
                        ->columnSpanFull()
                        ->columns(4)
                        ->schema([
                            $filters['customer'],
                            $filters['delivery'],
                            $filters['manager'],
                            $filters['branch'],
                        ]),
                ];
            })
            ->recordActions([
                ViewAction::make(),
                Action::make('invoice')
                    ->authorize('viewInvoice')
                    ->icon(Heroicon::OutlinedArrowDownCircle)
                    ->color('primary')
                    ->url(fn (Order $record) => route('web.order.invoice', ['id' => $record->id]), true),
                OrderActions::configure(true),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
