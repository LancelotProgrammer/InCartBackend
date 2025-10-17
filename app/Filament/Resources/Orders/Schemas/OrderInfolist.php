<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
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
                        TextEntry::make('coupon.title')->label('Coupon'),

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
                        TextEntry::make('cancel_reason'),
                    ]),

                Section::make('Config')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('notes'),
                        TextEntry::make('payment_method_id'),
                        TextEntry::make('delivery.name')->label('Delivery'),
                        TextEntry::make('delivery.phone')->label('Delivery Phone'),
                        TextEntry::make('delivery.email')->label('Delivery Email'),
                        TextEntry::make('delivery_scheduled_type'),
                        TextEntry::make('delivery_date'),
                        TextEntry::make('userAddress.title')->label('Address Title'),
                    ]),
            ]);
    }
}
