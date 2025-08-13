<?php

namespace Database\Factories;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Cart;
use App\Models\Coupon;
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

        // 3. Create cart for that user
        $cart = Cart::factory()->create([
            'title' => $this->faker->sentence(3),
        ]);

        // 4. Attach products from branch_product
        $branchProducts = BranchProduct::where('branch_id', $branch->id)
            ->inRandomOrder()
            ->take(rand(2, 5))
            ->get();

        $subtotal = 0;

        foreach ($branchProducts as $branchProduct) {
            $count = $this->faker->randomFloat(2, $branchProduct->minimum_order_quantity, $branchProduct->maximum_order_quantity);
            $cart->products()->attach($branchProduct->product_id, ['count' => $count]);
            $subtotal += $branchProduct->price * $count;
        }

        // 5. Coupon logic (50% chance)
        $applyCoupon = $this->faker->boolean(50);
        $coupon = null;
        $couponDiscount = 0;

        if ($applyCoupon) {
            $coupon = Coupon::inRandomOrder()->first();
            if ($coupon) {
                $couponDiscount = $coupon->config['discount_amount'] ?? 0;
            }
        }

        // 6. Random fees
        $deliveryFee = $this->faker->randomFloat(2, 0, 20);
        $serviceFee = $this->faker->randomFloat(2, 0, 10);
        $taxAmount = $this->faker->randomFloat(2, 0, 15);

        return [
            'order_number' => 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
            'notes' => $this->faker->optional()->sentence(),
            'order_status' => $this->faker->randomElement(OrderStatus::cases())->value,
            'payment_status' => $this->faker->randomElement(PaymentStatus::cases())->value,
            'delivery_status' => $this->faker->randomElement(DeliveryStatus::cases())->value,

            'subtotal_price' => round($subtotal, 2),
            'coupon_discount' => $couponDiscount,
            'delivery_fee' => $deliveryFee,
            'service_fee' => $serviceFee,
            'tax_amount' => $taxAmount,

            'detail_price' => round($subtotal, 2),
            'total_price' => round($subtotal - $couponDiscount + $deliveryFee + $serviceFee + $taxAmount, 2),

            'delivery_date' => $this->faker->boolean() ? $this->faker->optional()->dateTimeBetween('+1 days', '+1 month') : null,

            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'cart_id' => $cart->id,
            'coupon_id' => $coupon?->id,
            'payment_method_id' => PaymentMethod::inRandomOrder()->first()->id,
            'user_address_id' => $address->id,
        ];
    }
}
