<?php

namespace Database\Factories;

use App\Enums\DeliveryScheduledType;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\User;
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
        // 1. pick a random user who has addresses AND has role = customer
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
        $coupon = $applyCoupon ? Coupon::published()->inRandomOrder()->first() : null;
        $couponDiscount = $coupon?->config['value'] ?? 0;

        // 4. Fees
        $deliveryFee = fake()->randomFloat(2, 3, 20);
        $serviceFee = 2;
        $taxAmount = 0;

        // 5. Payment method
        $wasPaid = fake()->boolean(40);
        $payment = PaymentMethod::where('branch_id', $branch->id)->published()->inRandomOrder()->first();
        $token = null;
        if ($payment && $payment->code !== PaymentMethod::PAY_ON_DELIVERY_CODE) {
            do {
                $token = Str::random(32);
            } while (Order::where('payment_token', $token)->exists());
        } else {
            $wasPaid = false;
        }

        // 6. Delivery scheduling ratio
        $rand = fake()->numberBetween(1, 100);

        if ($rand <= 40) {
            // 40% past
            $timeContext = 'past';
            $date = Carbon::instance(fake()->dateTimeBetween('-1 month', '-1 day'));
        } elseif ($rand <= 60) {
            // 20% present
            $timeContext = 'present';
            $date = now();
        } else {
            // 40% future
            $timeContext = 'future';
            $date = Carbon::instance(fake()->dateTimeBetween('+1 day', '+7 day'));
        }

        // Delivery type
        $deliveryType = fake()->boolean(50)
            ? DeliveryScheduledType::SCHEDULED
            : DeliveryScheduledType::IMMEDIATE;

        // Delivery status
        $deliveryStatus = match ($deliveryType) {
            DeliveryScheduledType::IMMEDIATE => ($timeContext === 'past'
                ? DeliveryStatus::DELIVERED
                : DeliveryStatus::NOT_DELIVERED),
            DeliveryScheduledType::SCHEDULED => DeliveryStatus::SCHEDULED,
        };

        // 7. Prepare base order
        $order = [
            'order_number' => 'ORD-'.now()->format('YmdHis').'-'.strtoupper(Str::random(6)),
            'notes' => fake()->optional()->sentence(),
            'delivery_scheduled_type' => $deliveryType,
            'delivery_date' => $date,
            'payment_token' => $token,
            'discount_price' => $couponDiscount,
            'delivery_fee' => $deliveryFee,
            'service_fee' => $serviceFee,
            'tax_amount' => $taxAmount,
            'customer_id' => $user->id,
            'branch_id' => $branch->id,
            'coupon_id' => $coupon?->id,
            'payment_method_id' => $payment?->id,
            'user_address_id' => $address->id,
            'user_address_title' => $address->title,
        ];

        // 8. Status logic (based on time context)
        $status = match ($timeContext) {
            'past' => (
                $deliveryType === DeliveryScheduledType::SCHEDULED
                ? OrderStatus::PENDING
                : fake()->randomElement([
                    OrderStatus::FINISHED,
                    OrderStatus::CANCELLED,
                ])
            ),
            'present' => (
                $deliveryType === DeliveryScheduledType::SCHEDULED
                ? OrderStatus::PENDING
                : fake()->randomElement([
                    OrderStatus::PENDING,
                    OrderStatus::PROCESSING,
                    OrderStatus::DELIVERING,
                    OrderStatus::CLOSED,
                ])
            ),
            'future' => OrderStatus::PENDING,
        };

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
                    'payment_status' => $wasPaid ? PaymentStatus::PAID->value : PaymentStatus::UNPAID->value,
                    'delivery_status' => $deliveryStatus,
                ];
                break;

            case OrderStatus::CANCELLED:
                $order += [
                    'order_status' => OrderStatus::CANCELLED->value,
                    'payment_status' => $wasPaid
                        ? ($payment && $payment->code !== PaymentMethod::PAY_ON_DELIVERY_CODE ? PaymentStatus::REFUNDED->value : PaymentStatus::UNPAID->value)
                        : PaymentStatus::UNPAID->value,
                    'delivery_status' => DeliveryStatus::NOT_DELIVERED->value,
                    'manager_id' => $manager?->user_id,
                    'cancel_reason' => fake()->sentence(),
                    'cancelled_by_id' => fake()->boolean(50) ? $manager?->user_id : $user->id,
                ];
                break;

            case OrderStatus::PROCESSING:
                $order += [
                    'order_status' => OrderStatus::PROCESSING->value,
                    'payment_status' => $wasPaid ? PaymentStatus::PAID->value : PaymentStatus::UNPAID->value,
                    'delivery_status' => DeliveryStatus::NOT_DELIVERED->value,
                    'manager_id' => $manager?->user_id,
                ];
                break;

            case OrderStatus::DELIVERING:
                $order += [
                    'order_status' => OrderStatus::DELIVERING->value,
                    'payment_status' => $wasPaid ? PaymentStatus::PAID->value : PaymentStatus::UNPAID->value,
                    'delivery_status' => DeliveryStatus::OUT_FOR_DELIVERY->value,
                    'delivery_id' => $delivery->user_id,
                    'manager_id' => $manager->user_id,
                ];
                break;

            case OrderStatus::FINISHED:
                $order += [
                    'order_status' => OrderStatus::FINISHED->value,
                    'payment_status' => $wasPaid ? PaymentStatus::PAID->value : PaymentStatus::UNPAID->value,
                    'delivery_status' => DeliveryStatus::DELIVERED->value,
                    'delivery_id' => $delivery->user_id,
                    'manager_id' => $manager->user_id,
                ];
                break;

            case OrderStatus::CLOSED:
                $order += [
                    'order_status' => OrderStatus::CLOSED->value,
                    'payment_status' => PaymentStatus::PAID->value,
                    'delivery_status' => DeliveryStatus::DELIVERED->value,
                    'delivery_id' => $delivery->user_id,
                    'manager_id' => $manager->user_id,
                ];
                break;
        }

        return $order;
    }
}
