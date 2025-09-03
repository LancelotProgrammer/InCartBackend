<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order_number'),
                TextEntry::make('notes'),

                TextEntry::make('order_status')->badge(),
                TextEntry::make('payment_status')->badge(),
                TextEntry::make('delivery_status')->badge(),

                TextEntry::make('subtotal_price')->numeric(),
                TextEntry::make('coupon_discount')->numeric(),
                TextEntry::make('delivery_fee')->numeric(),
                TextEntry::make('service_fee')->numeric(),
                TextEntry::make('tax_amount')->numeric(),
                TextEntry::make('total_price')->numeric(),

                TextEntry::make('delivery_type')->badge(),
                TextEntry::make('delivery_date')->dateTime(),

                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),

                TextEntry::make('user.name'),
                TextEntry::make('branch.title'),
                TextEntry::make('cart.title'),
                TextEntry::make('coupon.title'),
                TextEntry::make('paymentMethod.title'),
                TextEntry::make('userAddress.title'),
            ]);
    }
}
