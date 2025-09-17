<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Enums\SettingType;
use App\Models\Advertisement;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\City;
use App\Models\Coupon;
use App\Models\File;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserNotification;
use Database\Factories\FavoriteFactory;
use Database\Factories\PackageFactory;
use Database\Factories\RootCategoryFactory;
use Database\Factories\SecondLevelCategoryFactory;
use Database\Factories\ThirdLevelCategoryFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class TestSeeder extends BaseSeeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $rootCategoryCount = 3;
        $productCount = 20;
        $productCategoryCount = 10;
        $branchAdvertisementCount = 5;
        $userCount = 20;
        $userAddressCount = 3;
        $userNotificationCount = 3;
        $favoriteCount = 3;
        $packageProductCount = 3;
        $productFileCount = 3;
        $categoryFileCount = 1;
        $advertisementFileCount = 1;
        $orderCount = 20;

        $this->command->info(PHP_EOL);
        $this->command->info('seeding static data');

        $this->command->info('seeding settings');
        Setting::create([
            'key' => 'test value',
            'value' => '100',
            'type' => SettingType::INT,
            'group' => 'test group',
            'is_locked' => false,
        ]);

        $this->command->info('seeding cities');
        City::insert([
            [
                'name' => json_encode(Factory::translations(['en', 'ar'], ['Jeddah', 'جدة']), JSON_UNESCAPED_UNICODE),
                'latitude' => 21.5292,
                'longitude' => 39.1611,
            ],
            [
                'name' => json_encode(Factory::translations(['en', 'ar'], ['Riyadh', 'رياض']), JSON_UNESCAPED_UNICODE),
                'latitude' => 24.7136,
                'longitude' => 46.6753,
            ],
            [
                'name' => json_encode(Factory::translations(['en', 'ar'], ['Makkah', 'مكة']), JSON_UNESCAPED_UNICODE),
                'latitude' => 21.4241,
                'longitude' => 39.8173,
            ],
        ]);

        $this->command->info('seeding roles');
        Role::insert([
            [
                'title' => 'super-admin',
                'code' => 'super-admin'
            ],
            [
                'title' => 'manager',
                'code' => 'manager'
            ],
            [
                'title' => 'delivery',
                'code' => 'delivery'
            ],
            [
                'title' => 'user',
                'code' => 'user'
            ],
        ]);

        $this->command->info('seeding permissions');
        Permission::insert([
            ['title' => 'view-all'],
            ['title' => 'view'],
            ['title' => 'create'],
            ['title' => 'edit'],
            ['title' => 'delete'],
        ]);

        $this->command->info('seeding roles permissions');
        $permissions = Permission::all();
        Role::all()->each(function ($role) use ($permissions) {
            $role->permissions()->syncWithoutDetaching($permissions->pluck('id'));
        });

        $this->command->info('seeding owner');
        User::insert([
            'name' => 'owner',
            'email' => 'owner@owner.com',
            'password' => Hash::make('dc8rqy0f6vasybipb'),
            'role_id' => Role::where('title', '=', 'super-admin')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id'),
        ]);

        $this->command->info('seeding managers');
        $managerPassword = Hash::make('man123456');
        User::insert([
            'name' => 'manager 1',
            'email' => 'manager1@manager1.com',
            'password' => $managerPassword,
            'role_id' => Role::where('title', '=', 'manager')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id'),
        ]);
        User::insert([
            'name' => 'manager 2',
            'email' => 'manager2@manager2.com',
            'password' => $managerPassword,
            'role_id' => Role::where('title', '=', 'manager')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Riyadh')->value('id'),
        ]);
        User::insert([
            'name' => 'manager 3',
            'email' => 'manager3@manager3.com',
            'password' => $managerPassword,
            'role_id' => Role::where('title', '=', 'manager')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Makkah')->value('id'),
        ]);

        $this->command->info('seeding delivery employees');
        $deliveryPassword = Hash::make('del123456');
        User::insert([
            'name' => 'delivery 1',
            'email' => 'delivery1@delivery1.com',
            'password' => $deliveryPassword,
            'role_id' => Role::where('title', '=', 'delivery')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id'),
        ]);
        User::insert([
            'name' => 'delivery 2',
            'email' => 'delivery2@delivery2.com',
            'password' => $deliveryPassword,
            'role_id' => Role::where('title', '=', 'delivery')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id'),
        ]);
        User::insert([
            'name' => 'delivery 3',
            'email' => 'delivery3@delivery3.com',
            'password' => $deliveryPassword,
            'role_id' => Role::where('title', '=', 'delivery')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Riyadh')->value('id'),
        ]);
        User::insert([
            'name' => 'delivery 4',
            'email' => 'delivery4@delivery4.com',
            'password' => $deliveryPassword,
            'role_id' => Role::where('title', '=', 'delivery')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Riyadh')->value('id'),
        ]);
        User::insert([
            'name' => 'delivery 5',
            'email' => 'delivery5@delivery5.com',
            'password' => $deliveryPassword,
            'role_id' => Role::where('title', '=', 'delivery')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Makkah')->value('id'),
        ]);
        User::insert([
            'name' => 'delivery 6',
            'email' => 'delivery6@delivery6.com',
            'password' => $deliveryPassword,
            'role_id' => Role::where('title', '=', 'delivery')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Makkah')->value('id'),
        ]);

        $this->command->info('seeding categories');
        RootCategoryFactory::new()
            ->has(File::factory()->count($categoryFileCount), 'files')
            ->count($rootCategoryCount)->create();
        $secondLevelCategories = SecondLevelCategoryFactory::new()
            ->has(File::factory()->count($categoryFileCount), 'files')
            ->count($rootCategoryCount * 10)->create();
        $thirdLevelCategories = ThirdLevelCategoryFactory::new()
            ->has(File::factory()->count($categoryFileCount), 'files')
            ->count($rootCategoryCount * 20)->create();
        $allCategoriesToAttach = $secondLevelCategories->concat($thirdLevelCategories);

        $this->command->info('seeding products');
        $products = Product::factory()
            ->has(File::factory()->count($productFileCount), 'files')
            ->count($productCount)
            ->create()
            ->each(function ($product) use ($allCategoriesToAttach, $productCategoryCount) {
                $product->categories()->attach(
                    $allCategoriesToAttach->random($productCategoryCount)->pluck('id')->toArray()
                );
            });

        $this->command->info('seeding branches');
        $branch = Branch::create([
            'title' => json_encode(Factory::translations(['en', 'ar'], ['branch number 1', 'الفرع رقم 1']), JSON_UNESCAPED_UNICODE),
            'description' => json_encode(Factory::translations(['en', 'ar'], ['branch number 1', 'الفرع رقم 1']), JSON_UNESCAPED_UNICODE),
            'latitude' => 21.5292,
            'longitude' => 39.1611,
            'is_default' => true,
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id')
        ]);
        $branch = Branch::create([
            'title' => json_encode(Factory::translations(['en', 'ar'], ['branch number 2', 'الفرع رقم 2']), JSON_UNESCAPED_UNICODE),
            'description' => json_encode(Factory::translations(['en', 'ar'], ['branch number 2', 'الفرع رقم 2']), JSON_UNESCAPED_UNICODE),
            'latitude' => 24.7136,
            'longitude' => 46.6753,
            'is_default' => true,
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Riyadh')->value('id')
        ]);
        $branch = Branch::create([
            'title' => json_encode(Factory::translations(['en', 'ar'], ['branch number 3', 'الفرع رقم 3']), JSON_UNESCAPED_UNICODE),
            'description' => json_encode(Factory::translations(['en', 'ar'], ['branch number 3', 'الفرع رقم 3']), JSON_UNESCAPED_UNICODE),
            'latitude' => 21.4241,
            'longitude' => 39.8173,
            'is_default' => true,
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Makkah')->value('id')
        ]);
        $branches = Branch::all();

        $this->command->info('seeding branches config');
        foreach ($branches as $branch) {
            foreach ($products as $product) {
                $minimumQuantity = fake()->numberBetween(1, 10);
                $maximumQuantity = fake()->numberBetween($minimumQuantity + 10, 50);
                $branch->products()->attach($product->id, [
                    'price' => fake()->randomFloat(2, 10, 500),
                    'discount' => fake()->numberBetween(0, 50),
                    'minimum_order_quantity' => $minimumQuantity,
                    'maximum_order_quantity' => $maximumQuantity,
                    'quantity' => fake()->numberBetween(100, 10000),
                    'expires_at' => fake()->dateTimeBetween('+10 days', '+1 year'),
                    'published_at' => fake()->dateTimeBetween('-1 year', '-10 days'),
                ]);
            }
            Advertisement::factory()
                ->count($branchAdvertisementCount)
                ->for($branch)
                ->has(File::factory()->count($advertisementFileCount), 'files')
                ->create();
        }

        $this->command->info('seeding coupons');
        foreach ($branches as $branch) {
            Coupon::insert([
                [
                    'title' => json_encode(Factory::translations(['en', 'ar'], ['test coupon', 'كوبون تجريبي']), JSON_UNESCAPED_UNICODE),
                    'description' => json_encode(Factory::translations(['en', 'ar'], ['test coupon', 'كوبون تجريبي']), JSON_UNESCAPED_UNICODE),
                    'code' => '123456',
                    'published_at' => now(),
                    'type' => CouponType::TIMED->value,
                    'config' => '{"value":15,"start_date":"2025-06-01","end_date":"2026-06-30","use_limit":5,"user_limit":100}',
                    'branch_id' => $branch->id,
                ],
                [
                    'title' => json_encode(Factory::translations(['en', 'ar'], ['test coupon', 'كوبون تجريبي']), JSON_UNESCAPED_UNICODE),
                    'description' => json_encode(Factory::translations(['en', 'ar'], ['test coupon', 'كوبون تجريبي']), JSON_UNESCAPED_UNICODE),
                    'code' => '1234567',
                    'published_at' => now(),
                    'type' => CouponType::TIMED->value,
                    'config' => '{"value":10,"start_date":"2025-09-01","end_date":"2026-06-30","use_limit":3,"user_limit":50}',
                    'branch_id' => $branch->id,
                ],
                [
                    'title' => json_encode(Factory::translations(['en', 'ar'], ['test coupon', 'كوبون تجريبي']), JSON_UNESCAPED_UNICODE),
                    'description' => json_encode(Factory::translations(['en', 'ar'], ['test coupon', 'كوبون تجريبي']), JSON_UNESCAPED_UNICODE),
                    'code' => '12345678',
                    'published_at' => now(),
                    'type' => CouponType::TIMED->value,
                    'config' => '{"value":5,"start_date":"2025-10-01","end_date":"2026-06-30","use_limit":2,"user_limit":25}',
                    'branch_id' => $branch->id,
                ],
            ]);
        }

        $this->command->info('seeding payment methods');
        foreach ($branches as $branch) {
            PaymentMethod::insert([
                [
                    'branch_id' => $branch->id,
                    'code' => 'pay-on-delivery',
                    'title' => json_encode(Factory::translations(['en', 'ar'], ['Pay on Delivery', 'الدفع عند الاستلام']), JSON_UNESCAPED_UNICODE),
                    'published_at' => now(),
                    'order' => '1',
                ],
                [
                    'branch_id' => $branch->id,
                    'code' => 'apple-pay',
                    'title' => json_encode(Factory::translations(['en', 'ar'], ['Apple Pay', 'Apple Pay']), JSON_UNESCAPED_UNICODE),
                    'published_at' => now(),
                    'order' => '2',
                ],
                [
                    'branch_id' => $branch->id,
                    'code' => 'google-pay',
                    'title' => json_encode(Factory::translations(['en', 'ar'], ['Google Pay', 'Google Pay']), JSON_UNESCAPED_UNICODE),
                    'published_at' => now(),
                    'order' => '2',
                ],
                [
                    'branch_id' => $branch->id,
                    'code' => 'mada-pay',
                    'title' => json_encode(Factory::translations(['en', 'ar'], ['Mada Pay', 'Mada Pay']), JSON_UNESCAPED_UNICODE),
                    'published_at' => now(),
                    'order' => '3',
                ],
                [
                    'branch_id' => $branch->id,
                    'code' => 'stc-pay',
                    'title' => json_encode(Factory::translations(['en', 'ar'], ['STC Pay', 'STC Pay']), JSON_UNESCAPED_UNICODE),
                    'published_at' => now(),
                    'order' => '3',
                ],
            ]);
        }

        $this->command->info('seeding users');
        User::factory($userCount)
            ->has(UserAddress::factory()->count($userAddressCount), 'addresses')
            ->has(UserNotification::factory()->count($userNotificationCount), 'notifications')
            ->has(
                FavoriteFactory::new()->count($favoriteCount)->sequence(fn($seq) => ['product_id' => $products[$seq->index % count($products)]->id]),
                'favorites'
            )
            ->has(PackageFactory::new()->hasAttached($products->random(rand(3, 7))->values())->count($packageProductCount), 'packages')
            ->create();

        $this->command->info('seeding orders');
        Order::factory()
            ->count($orderCount)
            ->create()
            ->each(function ($order) {
                $cart = Cart::factory()->create([
                    'order_number' => $order->order_number,
                    'order_id' => $order->id
                ]);
                CartProduct::factory()->count(rand(2, 10))->create(['cart_id' => $cart->id]);
                $subtotal = $cart->cartProducts->sum(function ($cartProduct) use ($order) {
                    $branchProduct = BranchProduct::where('branch_id', $order->branch_id)->where('product_id', $cartProduct->id)->first();
                    if (! $branchProduct) {
                        return 0;
                    }

                    return ($branchProduct->price - $branchProduct->discount ?? 0) * $cartProduct->quantity;
                });
                $order->update([
                    'subtotal_price' => $subtotal,
                    'total_price' => $subtotal - $order->coupon_discount + $order->delivery_fee + $order->service_fee + $order->tax_amount,
                ]);
            });
    }
}
