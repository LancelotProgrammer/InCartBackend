<?php

namespace Database\Factories;

use App\Enums\DeliveryStatus;
use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
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
        // 1. Pick a user who has an address
        $user = User::has('addresses')->with('addresses')->inRandomOrder()->first();
        $address = $user->addresses->random();

        // 2. Pick a branch in same city
        $branch = Branch::where('city_id', $address->city_id)->inRandomOrder()->first();

        // 3. Coupon logic (50% chance)
        $applyCoupon = $this->faker->boolean(50);
        $coupon = null;
        $couponDiscount = 0;

        if ($applyCoupon) {
            $coupon = Coupon::inRandomOrder()->first();
            if ($coupon) {
                $couponDiscount = $coupon->config['value'] ?? 0;
            }
        }

        // 4. Random fees
        $deliveryFee = $this->faker->randomFloat(2, 0, 20);
        $serviceFee = $this->faker->randomFloat(2, 0, 10);
        $taxAmount = $this->faker->randomFloat(2, 0, 15);
        
        // 5. Random payment method
        $payment = PaymentMethod::inRandomOrder()->first();
        $token = null;
        if ($payment->code !== 'pay-on-delivery') {
            do {
                $token = Str::random(32);
            } while (Order::where('payment_token', '=', $token)->exists());
        }
        
        // 5. Random delivery type
        $date = $this->faker->boolean() ? $this->faker->optional()->dateTimeBetween('+1 days', '+1 month') : null;
        if ($date === null) {
            $deliveryType = DeliveryType::IMMEDIATE;
            $deliveryStatus = DeliveryStatus::NOT_SHIPPED;
        } else {
            $deliveryType = DeliveryType::SCHEDULED;
            $deliveryStatus = DeliveryStatus::SCHEDULED;
        }

        return [
            'order_number' => 'ORD-'.now()->format('YmdHis').'-'.strtoupper(Str::random(6)),
            'notes' => $this->faker->optional()->sentence(),
            'delivery_type' => $deliveryType,
            'delivery_date' => $date,
            'payment_token' => $token,

            'order_status' => OrderStatus::PENDING->value,
            'payment_status' => PaymentStatus::UNPAID->value,
            'delivery_status' => $deliveryStatus,

            'coupon_discount' => $couponDiscount,
            'delivery_fee' => $deliveryFee,
            'service_fee' => $serviceFee,
            'tax_amount' => $taxAmount,

            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'coupon_id' => $coupon?->id,
            'payment_method_id' => $payment->id,
            'user_address_id' => $address->id,
        ];
    }
}
