<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\DeliveryStatus;
use App\Enums\DeliveryScheduledType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(5)
            ->components([
                TextInput::make('order_number')->required(),
                TextInput::make('notes'),

                Select::make('order_status')->options(OrderStatus::class)->required(),
                Select::make('payment_status')->options(PaymentStatus::class)->required(),
                Select::make('delivery_status')->options(DeliveryStatus::class)->required(),

                TextInput::make('subtotal_price')->required()->numeric()->default(0.0),
                TextInput::make('coupon_discount')->required()->numeric()->default(0.0),
                TextInput::make('delivery_fee')->required()->numeric()->default(0.0),
                TextInput::make('service_fee')->required()->numeric()->default(0.0),
                TextInput::make('tax_amount')->required()->numeric()->default(0.0),
                TextInput::make('total_price')->required()->numeric()->default(0.0),

                Select::make('delivery_scheduled_type')->options(DeliveryScheduledType::class)->required(),
                DateTimePicker::make('delivery_date'),

                Select::make('customer_id')->relationship('customer', 'name')->required(),
                Select::make('branch_id')->relationship('branch', 'title')->required(),
                Select::make('coupon_id')->relationship('coupon', 'title'), // filter based on branch
                Select::make('payment_method_id')->relationship('paymentMethod', 'title')->required(), // filter based on branch
                Select::make('user_address_id')->relationship('userAddress', 'title')->required(), // filter based on user
            ]);
    }
}
