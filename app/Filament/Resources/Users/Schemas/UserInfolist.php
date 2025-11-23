<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Information')
                    ->columns(5)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')->placeholder('No email'),
                        TextEntry::make('phone')->placeholder('No phone'),
                        TextEntry::make('city.name')->label('City'),
                        TextEntry::make('role.title')->label('Role'),
                    ]),
                Section::make('Customer Details Information')
                    ->visible(function ($record) {
                        return auth()->user()->canManageDeveloperSettings() && $record->role->code === Role::ROLE_CUSTOMER_CODE;
                    })
                    ->columns(1)
                    ->schema([
                        KeyValueEntry::make('Loyalty')
                            ->state(fn ($record) => [
                                'points' => $record->loyalty->points ?? '—',
                                'total_earned' => $record->loyalty->total_earned ?? '—',
                                'total_redeemed' => $record->loyalty->total_redeemed ?? '—',
                            ]),

                        RepeatableEntry::make('tickets')
                            ->grid(5)
                            ->columns(2)
                            ->schema([
                                TextEntry::make('question')->label('Question'),
                                TextEntry::make('reply')->label('Reply'),
                            ])
                            ->state(fn (User $record) => $record->tickets->map(fn ($ticket) => [
                                'question' => $ticket->question,
                                'reply' => $ticket->reply ?? 'No reply',
                            ])),

                        RepeatableEntry::make('feedback')
                            ->grid(5)
                            ->columns(1)
                            ->schema([
                                TextEntry::make('feedback')->label('feedback'),
                            ])
                            ->state(fn (User $record) => $record->feedback->map(fn ($ticket) => [
                                'feedback' => $ticket->feedback,
                            ])),

                        TextEntry::make('gifts')
                            ->label('Gifts')
                            ->state(fn (User $record) => $record->gifts->pluck('title')->join(', ')),

                        RepeatableEntry::make('notifications')
                            ->grid(4)
                            ->schema([
                                TextEntry::make('title')->label('Title'),
                            ])
                            ->state(fn (User $record) => $record->userNotifications->map(fn ($notification) => [
                                'title' => $notification->title,
                                'body' => $notification->body,
                            ])),

                        RepeatableEntry::make('Addresses')
                            ->grid(3)
                            ->columns(3)
                            ->schema([
                                TextEntry::make('title')->label('Title'),
                                TextEntry::make('city')->label('City'),
                                TextEntry::make('phone')->label('Phone'),
                            ])
                            ->state(fn (User $record) => $record->addresses->map(fn ($addr) => [
                                'title' => $addr->title,
                                'city' => $addr->city->name,
                                'phone' => $addr->phone,
                            ])),

                        RepeatableEntry::make('Orders')
                            ->columns(3)
                            ->grid(3)
                            ->schema([
                                TextEntry::make('order_number')->label('Order number'),
                                TextEntry::make('total_price')->label('Total price'),
                                TextEntry::make('payment_method')->label('Payment method'),

                                TextEntry::make('order_status')->label('Order status')->badge(),
                                TextEntry::make('delivery_status')->label('Delivery status')->badge(),
                                TextEntry::make('payment_status')->label('Payment status')->badge(),

                                TextEntry::make('user_address')->label('User address'),
                                TextEntry::make('coupon')->label('Coupon'),
                                TextEntry::make('delivery_date')->label('Delivery date')->date(),

                                TextEntry::make('cart')->label('Cart')->columnSpanFull(),
                            ])
                            ->state(fn (User $record) => $record->customerOrders->map(fn (Order $order) => [
                                'order_number' => $order->order_number,
                                'total_price' => $order->total_price,
                                'payment_method' => $order->paymentMethod->title,

                                'order_status' => $order->order_status,
                                'delivery_status' => $order->delivery_status,
                                'payment_status' => $order->payment_status,

                                'user_address' => $order->user_address_title,
                                'coupon' => $order->coupon?->title ?? 'no coupon',
                                'delivery_date' => $order->delivery_date,

                                'cart' => $order->carts
                                    ->first()?->cartProducts
                                    ->map(fn ($cartProducts) => "{$cartProducts->title} (x{$cartProducts->quantity})")
                                    ->join(', ') ?? '—',
                            ])),

                        RepeatableEntry::make('Favorites')
                            ->columns(1)
                            ->grid(5)
                            ->schema([
                                TextEntry::make('title')->label('Title'),
                            ])
                            ->state(fn ($record) => $record->favorites->map(fn ($fav) => [
                                'title' => $fav->product->title,
                            ])),

                        RepeatableEntry::make('Packages')
                            ->columns(2)
                            ->grid(4)
                            ->schema([
                                TextEntry::make('title')->label('Title'),
                                TextEntry::make('products')->label('Products'),
                            ])
                            ->state(fn ($record) => $record->packages->map(fn ($pkg) => [
                                'title' => $pkg->title,
                                'products' => $pkg->products->pluck('title')->join(', '),
                            ])),
                    ]),
            ]);
    }
}
