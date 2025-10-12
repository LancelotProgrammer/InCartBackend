<?php

namespace Database\Factories;

use App\Enums\{
    OrderStatus,
    PaymentStatus,
    DeliveryStatus,
    DeliveryScheduledType
};
use App\Models\{
    User,
    Role,
    Branch,
    BranchUser,
    Coupon,
    PaymentMethod,
    Order
};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // 1. pick a random user who has addresses AND has role = user
        $user = User::whereHas('addresses')
            ->whereHas('role', fn ($query) => $query->where('code', Role::ROLE_CUSTOMER_CODE))
            ->with('addresses')
            ->inRandomOrder()
            ->first();

        $address = $user->addresses->random();

        // 2. Pick a branch in the same city
        $branch = Branch::where('city_id', $address->city_id)
            ->inRandomOrder()
            ->first();

        // 3. Coupon logic (30%)
        $applyCoupon = fake()->boolean(30);
        $coupon = $applyCoupon ? Coupon::inRandomOrder()->first() : null;
        $couponDiscount = $coupon?->config['value'] ?? 0;

        // 4. Fees
        $deliveryFee = fake()->randomFloat(2, 3, 20);
        $serviceFee = 2;
        $taxAmount = 2;
        
        // 5. Payment method
        $wasPaid = fake()->boolean(40);
        $payment = PaymentMethod::where('branch_id', $branch->id)->inRandomOrder()->first();
        $token = null;
        if ($payment && $payment->code !== 'pay-on-delivery') {
            do {
                $token = Str::random(32);
            } while (Order::where('payment_token', $token)->exists());
        }

        // 6. Delivery scheduling
        $now = now();
        $date = fake()->boolean(30)
            ? Carbon::instance(fake()->dateTimeBetween('+1 days', '+1 month'))
            : $now;

        $deliveryType = $date->equalTo($now)
            ? DeliveryScheduledType::IMMEDIATE
            : DeliveryScheduledType::SCHEDULED;

        $deliveryStatus = $date->equalTo($now)
            ? DeliveryStatus::NOT_SHIPPED
            : DeliveryStatus::SCHEDULED;

        // 7. Base order
        $order = [
            'order_number' => 'ORD-'.now()->format('YmdHis').'-'.strtoupper(Str::random(6)),
            'notes' => fake()->optional()->sentence(),
            'delivery_scheduled_type' => $deliveryType,
            'delivery_date' => $date,
            'payment_token' => $token,
            'coupon_discount' => $couponDiscount,
            'delivery_fee' => $deliveryFee,
            'service_fee' => $serviceFee,
            'tax_amount' => $taxAmount,
            'customer_id' => $user->id,
            'branch_id' => $branch->id,
            'coupon_id' => $coupon?->id,
            'payment_method_id' => $payment?->id,
            'user_address_id' => $address->id,
        ];

        // 8. Assign random valid status set
        $status = fake()->randomElement([
            OrderStatus::PENDING,
            OrderStatus::CANCELLED,
            OrderStatus::PROCESSING,
            OrderStatus::DELIVERING,
            OrderStatus::FINISHED,
        ]);

        // 9. Manager & delivery users for this branch
        $manager = BranchUser::where('branch_id', $branch->id)
            ->whereHas('user.role', fn ($q) => $q->where('code', Role::ROLE_MANAGER_CODE))
            ->inRandomOrder()
            ->first();

        $delivery = BranchUser::where('branch_id', $branch->id)
            ->whereHas('user.role', fn ($q) => $q->where('code', Role::ROLE_DELIVERY_CODE))
            ->inRandomOrder()
            ->first();

        // 10. Apply status-based logic
        switch ($status) {
            case OrderStatus::PENDING:
                $order += [
                    'order_status' => OrderStatus::PENDING->value,
                    'payment_status' => $wasPaid
                        ? PaymentStatus::PAID->value
                        : PaymentStatus::UNPAID->value,
                    'delivery_status' => $deliveryStatus,
                ];
                break;

            case OrderStatus::CANCELLED:
                $order += [
                    'order_status' => OrderStatus::CANCELLED->value,
                    'payment_status' => $wasPaid
                        ? ($payment && $payment->code !== 'pay-on-delivery' ? PaymentStatus::REFUNDED->value : PaymentStatus::UNPAID->value)
                        : PaymentStatus::UNPAID->value,
                    'delivery_status' => DeliveryStatus::NOT_SHIPPED->value,
                    'manager_id' => $manager?->user_id,
                    'cancel_reason' => fake()->sentence(),
                ];
                break;

            case OrderStatus::PROCESSING:
                $order += [
                    'order_status' => OrderStatus::PROCESSING->value,
                    'payment_status' => $wasPaid
                        ? PaymentStatus::PAID->value
                        : PaymentStatus::UNPAID->value,
                    'delivery_status' => DeliveryStatus::NOT_SHIPPED->value,
                    'manager_id' => $manager?->user_id,
                ];
                break;

            case OrderStatus::DELIVERING:
                $order += [
                    'order_status' => OrderStatus::DELIVERING->value,
                    'payment_status' => $wasPaid
                        ? PaymentStatus::PAID->value
                        : PaymentStatus::UNPAID->value,
                    'delivery_status' => DeliveryStatus::OUT_FOR_DELIVERY->value,
                    'delivery_id' => $delivery?->user_id,
                    'manager_id' => $manager?->user_id,
                ];
                break;

            case OrderStatus::FINISHED:
                $order += [
                    'order_status' => OrderStatus::FINISHED->value,
                    'payment_status' => PaymentStatus::PAID->value,
                    'delivery_status' => DeliveryStatus::DELIVERED->value,
                    'manager_id' => $manager?->user_id,
                ];
                break;
        }

        return $order;
    }
}
