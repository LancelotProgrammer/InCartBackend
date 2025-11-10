<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\DeliveryScheduledType;
use App\Services\OrderService;
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
                    ->columns(6)
                    ->schema([
                        TextEntry::make('order_number')->placeholder('No order number available'),
                        TextEntry::make('order_status')->badge()->placeholder('No status'),
                        TextEntry::make('payment_status')->badge()->placeholder('No payment info'),
                        TextEntry::make('delivery_status')->badge()->placeholder('No delivery status'),
                        TextEntry::make('total_price')->money('SAR')->placeholder('—'),
                        TextEntry::make('created_at')->dateTime()->placeholder('No creation date'),
                        
                        TextEntry::make('subtotal_price')->money('SAR')->placeholder('—'),
                        TextEntry::make('discount_price')->money('SAR')->placeholder('—'),
                        TextEntry::make('delivery_fee')->money('SAR')->placeholder('—'),
                        TextEntry::make('service_fee')->money('SAR')->placeholder('—'),
                        TextEntry::make('tax_amount')->money('SAR')->placeholder('—'),
                        TextEntry::make('payed_price')->money('SAR')->placeholder('—'),
                        
                        TextEntry::make('customer.name')->label('Customer')->placeholder('No customer'),
                        TextEntry::make('customer.phone')->label('Customer Phone')->placeholder('No customer phone'),
                        TextEntry::make('customer.approved_at')->label('Customer Approved')->placeholder('Customer is not marked as approved'),
                        TextEntry::make('customer.loyalty.total_earned')->label('Loyalty Points')->placeholder('Customer has no loyalty points'),
                        TextEntry::make('cancelledBy.name')->label('Cancelled By')->placeholder('No cancelled by'),
                        TextEntry::make('cancel_reason')->placeholder('No cancel reason'),
                        
                        TextEntry::make('manager.name')->label('Manager')->placeholder('No manager assigned'),
                        TextEntry::make('delivery.name')->label('Delivery')->placeholder('No delivery assigned'),
                        TextEntry::make('delivery.phone')->label('Delivery Phone')->placeholder('No delivery phone'),
                        TextEntry::make('delivery.email')->label('Delivery Email')->placeholder('No delivery email'),
                        TextEntry::make('coupon.title')->label('Coupon')->placeholder('No coupon used'),
                        TextEntry::make('branch.title')->label('Branch')->placeholder('No branch assigned'),
                    ]),

                Section::make('Edit')
                    ->columns(3)
                    ->schema([
                        TextInput::make('notes')->columnSpan(2),
                        Select::make('payment_method_id')
                            ->required()
                            ->options(function (Get $get) {
                                return OrderService::getPaymentMethods($get('branch_id'))->pluck('title', 'id');
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
                            ->minDate(now()->inApplicationTimezone()),
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
