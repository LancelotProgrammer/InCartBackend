<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\DeliveryScheduledType;
use App\Enums\OrderStatus;
use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderManager;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Info')
                    ->columns(11)
                    ->schema([
                        TextEntry::make('order_number'),
                        TextEntry::make('cancel_reason'),

                        TextEntry::make('order_status')->badge(),
                        TextEntry::make('payment_status')->badge(),
                        TextEntry::make('delivery_status')->badge(),

                        TextEntry::make('subtotal_price')->money('SAR'),
                        TextEntry::make('discount_price')->money('SAR'),
                        TextEntry::make('delivery_fee')->money('SAR'),
                        TextEntry::make('service_fee')->money('SAR'),
                        TextEntry::make('tax_amount')->money('SAR'),
                        TextEntry::make('total_price')->money('SAR'),

                        TextEntry::make('created_at')->dateTime(),

                        TextEntry::make('customer.name')->label('Customer'),
                        TextEntry::make('customer.phone')->label('Customer Phone'),
                        TextEntry::make('customer.email')->label('Customer Email'),
                        TextEntry::make('delivery.name')->label('Delivery'),
                        TextEntry::make('delivery.phone')->label('Delivery Phone'),
                        TextEntry::make('delivery.email')->label('Delivery Email'),
                        TextEntry::make('manager.name')->label('Manager'),
                        TextEntry::make('branch.title')->label('Branch'),
                        TextEntry::make('coupon.title')->label('Coupon'),
                    ]),

                Section::make('Edit')
                    ->columns(3)
                    ->schema([
                        TextInput::make('notes'),
                        Select::make('payment_method_id')
                            ->relationship(
                                'paymentMethod',
                                'title',
                                fn (Builder $query, Get $get) => $query->where('branch_id', '=', $get('branch_id'))
                            )
                            ->required(),
                        Select::make('delivery_id')
                            ->visible(function (Order $order) {
                                return $order->order_status === OrderStatus::PROCESSING;
                            })
                            ->options(function (Get $get) {
                                return OrderManager::getDeliveryUsers($get('branch_id'));
                            }),
                        Select::make('delivery_scheduled_type')
                            ->afterStateUpdated(function (Set $set) {
                                $set('delivery_date', null);
                            })
                            ->options(DeliveryScheduledType::class)
                            ->required()
                            ->live(),
                        DateTimePicker::make('delivery_date')
                            ->required(function (Get $get) {
                                return $get('delivery_scheduled_type') === DeliveryScheduledType::SCHEDULED;
                            })
                            ->disabled(function (Get $get) {
                                return $get('delivery_scheduled_type') === DeliveryScheduledType::IMMEDIATE;
                            })
                            ->minDate(now()),
                        Select::make('user_address_id')
                            ->relationship(
                                'userAddress',
                                'title',
                                fn (Builder $query, Get $get) => $query->where('user_id', '=', $get('customer_id'))
                            )
                            ->required(),
                    ]),
            ]);
    }
}
