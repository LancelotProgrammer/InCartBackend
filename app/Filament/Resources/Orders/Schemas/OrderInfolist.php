<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(5)
            ->components([
                TextEntry::make('order_number'),
                TextEntry::make('notes'),
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

                TextEntry::make('delivery_scheduled_type')->badge(),
                TextEntry::make('delivery_date')->dateTime(),

                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),

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
                TextEntry::make('userAddress.title')->label('User Address'),
            ]);
    }
}
