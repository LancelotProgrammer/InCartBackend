<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\DeliveryScheduledType;
use App\Models\Role;
use App\Models\User;
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

                        TextEntry::make('subtotal_price')->numeric(),
                        TextEntry::make('coupon_discount')->numeric(),
                        TextEntry::make('delivery_fee')->numeric(),
                        TextEntry::make('service_fee')->numeric(),
                        TextEntry::make('tax_amount')->numeric(),
                        TextEntry::make('total_price')->numeric(),

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
                        TextEntry::make('paymentMethod.title')->label('Payment Method'),
                    ]),

                Section::make('Edit')
                    ->columns(2)
                    ->schema([
                        TextInput::make('notes')->columnSpanFull(),
                        Select::make('delivery_id')->options(User::where('role_id', '=', Role::where('code', '=', 'delivery')->first()->id)->pluck('name', 'id')),
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
                            }),
                        Select::make('user_address_id')
                            ->relationship(
                                'userAddress',
                                'title',
                                fn(Builder $query, Get $get) => $query->where('user_id', '=', $get('customer_id'))
                            )
                            ->required(),
                    ]),
            ]);
    }
}
