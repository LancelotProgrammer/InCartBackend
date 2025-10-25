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
                        TextEntry::make('order_number')->placeholder('No order number available'),
                        TextEntry::make('coupon.title')->label('Coupon')->placeholder('No coupon used'),
                        TextEntry::make('order_status')->badge()->placeholder('No status'),
                        TextEntry::make('payment_status')->badge()->placeholder('No payment info'),
                        TextEntry::make('delivery_status')->badge()->placeholder('No delivery status'),
                        TextEntry::make('subtotal_price')->money('SAR')->placeholder('—'),
                        TextEntry::make('discount_price')->money('SAR')->placeholder('No discount applied'),
                        TextEntry::make('delivery_fee')->money('SAR')->placeholder('No delivery fee'),
                        TextEntry::make('service_fee')->money('SAR')->placeholder('No service fee'),
                        TextEntry::make('tax_amount')->money('SAR')->placeholder('No tax applied'),
                        TextEntry::make('total_price')->money('SAR')->placeholder('No total calculated'),
                        TextEntry::make('created_at')->dateTime()->placeholder('No creation date'),
                        TextEntry::make('customer.name')->label('Customer')->placeholder('No customer'),
                        TextEntry::make('customer.phone')->label('Customer Phone')->placeholder('No customer phone'),
                        TextEntry::make('customer.email')->label('Customer Email')->placeholder('No customer email'),
                        TextEntry::make('delivery.name')->label('Delivery')->placeholder('No delivery assigned'),
                        TextEntry::make('delivery.phone')->label('Delivery Phone')->placeholder('No delivery phone'),
                        TextEntry::make('delivery.email')->label('Delivery Email')->placeholder('No delivery email'),
                        TextEntry::make('manager.name')->label('Manager')->placeholder('No manager assigned'),
                        TextEntry::make('branch.title')->label('Branch')->placeholder('No branch assigned'),
                        TextEntry::make('cancel_reason')->placeholder('No cancel reason'),
                    ]),

                Section::make('Config')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('notes')->placeholder('No notes'),
                        TextEntry::make('paymentMethod.title')->label('Payment Method')->placeholder('No payment method'),
                        TextEntry::make('delivery.name')->label('Delivery')->placeholder('No delivery assigned'),
                        TextEntry::make('delivery.phone')->label('Delivery Phone')->placeholder('No delivery phone'),
                        TextEntry::make('delivery.email')->label('Delivery Email')->placeholder('No delivery email'),
                        TextEntry::make('delivery_scheduled_type')->label('Delivery Type')->placeholder('No delivery type'),
                        TextEntry::make('delivery_date')->date()->placeholder('No delivery date scheduled'),
                        TextEntry::make('userAddress.title')->label('Address Title')->placeholder('No address selected'),

                    ]),
            ]);
    }
}
