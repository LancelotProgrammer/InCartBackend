<?php

namespace App\Filament\Actions;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\ExternalServices\FirebaseFCM;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Branch;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Notifications\DeliveryOrderNotification;
use App\Services\Cache;
use App\Services\DatabaseUserNotification;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OrderActions
{
    public static function configure(bool $hasEditAction): ActionGroup
    {
        // Edit order
        $editAction = EditAction::make();

        $actions = [

            // Cancel order
            Action::make('cancel')
                ->authorize('cancel')
                ->icon(Heroicon::XCircle)
                ->color('danger')
                ->requiresConfirmation()
                ->visible(function (Order $order) {
                    return $order->isCancelable();
                })
                ->schema([
                    Textarea::make('cancel_reason')->required(),
                ])
                ->action(function (Order $order, array $data) {
                    $order->update([
                        'order_status' => OrderStatus::CANCELLED,
                        'payment_status' => $order->payment_status === PaymentStatus::PAID ? PaymentStatus::REFUNDED : PaymentStatus::UNPAID,
                        'delivery_status' => DeliveryStatus::NOT_SHIPPED,
                        'manager_id' => auth()->user()->id,
                        'cancel_reason' => $data['cancel_reason'],
                    ]);
                    $order->save();
                    FirebaseFCM::sendOrderStatusNotification($order);
                    DatabaseUserNotification::sendOrderStatusNotification($order);
                    Cache::deletePendingOrderCount();
                    Notification::make()
                        ->title("Order #{$order->order_number} has been cancelled.")
                        ->success()
                        ->send();
                }),

            // Approve order
            Action::make('approve')
                ->authorize('approve')
                ->icon(Heroicon::CheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->visible(function (Order $order) {
                    return $order->order_status === OrderStatus::PENDING;
                })
                ->action(function (Order $order) {
                    if (
                        PaymentMethod::where('id', '=', $order->payment_method_id)->value('code') !== 'pay-on-delivery' &&
                        $order->payment_status === PaymentStatus::UNPAID
                    ) {
                        Notification::make()
                            ->title("Order #{$order->order_number} cannot be approved.")
                            ->body('Order is not checked out')
                            ->warning()
                            ->send();

                        return;
                    }
                    if (! $order->delivery_date->isSameDay(now())) {
                        Notification::make()
                            ->title("Order #{$order->order_number} cannot be approved.")
                            ->body('Order cannot be approved because it was not created today.')
                            ->warning()
                            ->send();

                        return;
                    }
                    $order->update([
                        'order_status' => OrderStatus::PROCESSING,
                        'manager_id' => auth()->user()->id,
                    ]);
                    $order->save();
                    FirebaseFCM::sendOrderStatusNotification($order);
                    DatabaseUserNotification::sendOrderStatusNotification($order);
                    Cache::deletePendingOrderCount();
                    Notification::make()
                        ->title("Order #{$order->order_number} is approved and currently processing.")
                        ->success()
                        ->send();
                }),

            // Send to delivery
            Action::make('select_delivery')
                ->authorize('selectDelivery')
                ->icon(Heroicon::Truck)
                ->color('info')
                ->requiresConfirmation()
                ->visible(function (Order $order) {
                    return $order->order_status === OrderStatus::PROCESSING;
                })
                ->schema([
                    Select::make('delivery_id')->options(function ($record) {
                        return User::getUsersWhoCanBeAssignedToTakeOrders()->where(function (Builder $query) use ($record) {
                            $branch = Branch::find($record->branch_id);
                            $query->whereHas('branches', function (Builder $q) use ($branch) {
                                $q->where('branch_id', '=', $branch->id);
                            });
                        })->pluck('name', 'id');
                    }),
                ])
                ->action(function (Order $order, array $data) {
                    $order->update([
                        'order_status' => OrderStatus::DELIVERING,
                        'delivery_status' => DeliveryStatus::OUT_FOR_DELIVERY,
                        'delivery_id' => $data['delivery_id'],
                        'manager_id' => auth()->user()->id,
                    ]);
                    $order->save();
                    FirebaseFCM::sendOrderStatusNotification($order);
                    DatabaseUserNotification::sendOrderStatusNotification($order);
                    User::where('id', '=',$data['delivery_id'])->first()->notify(new DeliveryOrderNotification($order));
                    Cache::deletePendingOrderCount();
                    Notification::make()
                        ->title("Order #{$order->order_number} is out for delivery.")
                        ->info()
                        ->send();
                }),

            // Mark as finished
            Action::make('finish')
                ->authorize('finish')
                ->icon(Heroicon::DocumentCheck)
                ->color('success')
                ->requiresConfirmation()
                ->visible(function (Order $order) {
                    return $order->order_status === OrderStatus::DELIVERING;
                })
                ->action(function (Order $order) {
                    $order->update([
                        'order_status' => OrderStatus::FINISHED,
                        'delivery_status' => DeliveryStatus::DELIVERED,
                        'payment_status' => PaymentStatus::PAID,
                        'manager_id' => auth()->user()->id,
                    ]);
                    $order->save();
                    FirebaseFCM::sendOrderStatusNotification($order);
                    DatabaseUserNotification::sendOrderStatusNotification($order);
                    Cache::deletePendingOrderCount();
                    Notification::make()
                        ->title("Order #{$order->order_number} has been completed.")
                        ->success()
                        ->send();
                }),

            // Archive order
            Action::make('archive')
                ->authorize('archive')
                ->icon(Heroicon::ArchiveBox)
                ->color('warning')
                ->requiresConfirmation()
                ->visible(function (Order $order) {
                    return $order->order_status === OrderStatus::FINISHED || $order->order_status === OrderStatus::CANCELLED;
                })
                ->action(function (Order $order) {
                    DB::table('order_archives')->insert([
                        'archived_at' => now(),
                        'order_number' => $order->order_number,
                        'notes' => $order->notes,
                        'order_status' => $order->order_status->value,
                        'payment_status' => $order->payment_status->value,
                        'delivery_status' => $order->delivery_status->value,
                        'subtotal_price' => $order->subtotal_price,
                        'coupon_discount' => $order->coupon_discount,
                        'delivery_fee' => $order->delivery_fee,
                        'service_fee' => $order->service_fee,
                        'tax_amount' => $order->tax_amount,
                        'total_price' => $order->total_price,
                        'delivery_scheduled_type' => $order->delivery_scheduled_type->value,
                        'delivery_date' => $order->delivery_date,
                        'payment_token' => $order->payment_token,
                        'created_at' => $order->created_at,
                        'updated_at' => $order->updated_at,
                        'customer' => $order->customer->toJson(),
                        'delivery' => $order->delivery?->toJson(),
                        'manager' => $order->manager?->toJson(),
                        'branch' => $order->branch->toJson(),
                        'coupon' => $order->coupon?->toJson(),
                        'payment_method' => $order->paymentMethod->toJson(),
                        'user_address' => $order->userAddress->toJson(),
                        'cart' => $order->carts()->with('cartProducts.product')->get()->toJson(),
                    ]);
                    $order->delete();
                    Notification::make()
                        ->title("Order #{$order->order_number} has been archived.")
                        ->warning()
                        ->send();
                }),
        ];

        if ($hasEditAction) {
            $actions[] = $editAction;
        }

        return ActionGroup::make($actions)
            ->label('More actions')
            ->icon('heroicon-m-ellipsis-vertical')
            ->size(Size::Small)
            ->color('primary')
            ->button();
    }
}
