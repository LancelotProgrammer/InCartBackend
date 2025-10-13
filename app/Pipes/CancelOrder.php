<?php

namespace App\Pipes;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\LogicalException;
use App\Models\Order;
use App\Services\Cache;
use App\Services\DatabaseManagerNotification;
use Closure;
use Illuminate\Http\Request;

class CancelOrder
{
    public function __invoke(Request $request, Closure $next): array
    {
        $request->validate([
            'reason' => 'nullable|string',
        ]);

        $order = Order::where('id', $request->route('id'))
            ->where('customer_id', $request->user()->id)
            ->first();

        if (! $order) {
            throw new LogicalException('Order not found', 'The order ID does not exist or does not belong to the current user.');
        }

        if (! $order->isCancelable()) {
            throw new LogicalException('Order can not be canceled');
        }

        $order->update([
            'order_status' => OrderStatus::CANCELLED,
            'payment_status' => $order->payment_status === PaymentStatus::PAID ? PaymentStatus::REFUNDED : PaymentStatus::UNPAID,
            'delivery_status' => DeliveryStatus::NOT_DELIVERED,
            'cancel_reason' => $request->input('cancel_reason'),
        ]);
        $order->save();

        DatabaseManagerNotification::sendCancelledOrderNotification($order);

        Cache::deletePendingOrderCount();

        return $next([]);
    }
}
