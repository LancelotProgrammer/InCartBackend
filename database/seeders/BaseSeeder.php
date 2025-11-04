<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Enums\OrderStatus;
use App\Models\Advertisement;
use App\Models\AdvertisementUser;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\City;
use App\Models\Coupon;
use App\Models\Feedback;
use App\Models\File;
use App\Models\Gift;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserNotification;
use Database\Factories\FavoriteFactory;
use Database\Factories\PackageFactory;
use Database\Factories\RootCategoryFactory;
use Database\Factories\SecondLevelCategoryFactory;
use Database\Factories\ThirdLevelCategoryFactory;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class BaseSeeder extends Seeder
{
    /**
     * The current Faker instance.
     *
     * @var Generator
     */
    protected $faker;

    /**
     * Create a new seeder instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->faker = $this->withFaker();
    }

    /**
     * Get a new Faker instance.
     *
     * @return Generator
     */
    protected function withFaker()
    {
        return Container::getInstance()->make(Generator::class);
    }

    public function generateData(
        int $rootCategoryCount,
        int $productCount,
        int $productCategoryCount,
        int $branchAdvertisementCount,
        int $userCount,
        int $userAddressCount,
        int $userNotificationCount,
        int $favoriteCount,
        int $packageProductCount,
        int $productFileCount,
        int $categoryFileCount,
        int $advertisementFileCount,
        int $orderCount
    ): void {
        $this->command->info(PHP_EOL);
        $this->command->info('seeding data');

        $this->command->info('seeding settings');
        $this->call(SettingsSeeder::class, true);

        $this->command->info('seeding cities');
        City::insert([
            [
                'name' => json_encode(Factory::translations(['en', 'ar'], ['Jeddah', 'جدة']), JSON_UNESCAPED_UNICODE),
                'boundary' => '[{"latitude": 21.24586234991347, "longitude": 38.97399902343751, "name": "bl"}, {"latitude": 21.879341082799023, "longitude": 38.97399902343751, "name": "tl"}, {"latitude": 21.879341082799023, "longitude": 39.32281494140626, "name": "tr"}, {"latitude": 21.24586234991347, "longitude": 39.32281494140626, "name": "br"}, {"latitude": 21.562601716356248, "longitude": 39.14840698242188, "name": "c"}]',
            ],
            [
                'name' => json_encode(Factory::translations(['en', 'ar'], ['Riyadh', 'رياض']), JSON_UNESCAPED_UNICODE),
                'boundary' => '[{"latitude": 24.37211730011131, "longitude": 46.39251708984376, "name": "bl"}, {"latitude": 25.07316070640961, "longitude": 46.39251708984376, "name": "tl"}, {"latitude": 25.07316070640961, "longitude": 46.97479248046876, "name": "tr"}, {"latitude": 24.37211730011131, "longitude": 46.97479248046876, "name": "br"}, {"latitude": 24.72263900326046, "longitude": 46.68365478515626, "name": "c"}]',
            ],
            [
                'name' => json_encode(Factory::translations(['en', 'ar'], ['Makkah', 'مكة']), JSON_UNESCAPED_UNICODE),
                'boundary' => '[{"latitude": 21.33926933366899, "longitude": 39.72793579101563, "name": "bl"}, {"latitude": 21.52718223333923, "longitude": 39.72793579101563, "name": "tl"}, {"latitude": 21.52718223333923, "longitude": 39.90234375000001, "name": "tr"}, {"latitude": 21.33926933366899, "longitude": 39.90234375000001, "name": "br"}, {"latitude": 21.433225783504113, "longitude": 39.81513977050782, "name": "c"}]',
            ],
        ]);

        $this->command->info('seeding roles');
        Role::insert([
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Super Admin', 'مالك']), JSON_UNESCAPED_UNICODE),
                'code' => Role::ROLE_SUPER_ADMIN_CODE,
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Developer', 'مطور']), JSON_UNESCAPED_UNICODE),
                'code' => Role::ROLE_DEVELOPER_CODE,
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Manager', 'مدير']), JSON_UNESCAPED_UNICODE),
                'code' => Role::ROLE_MANAGER_CODE,
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delivery', 'موظف توصيل']), JSON_UNESCAPED_UNICODE),
                'code' => Role::ROLE_DELIVERY_CODE,
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Customer', 'عميل']), JSON_UNESCAPED_UNICODE),
                'code' => Role::ROLE_CUSTOMER_CODE,
            ],
        ]);

        $this->command->info('seeding permissions');
        $this->call(PermissionsSeeder::class, true);

        $this->command->info('seeding roles permissions');
        $rolesPermissions = [
            Role::ROLE_SUPER_ADMIN_CODE => Permission::all()
                ->whereNotIn(
                    'code',
                    [
                        'can-receive-order-notifications',
                        'can-be-assigned-to-take-orders',
                        'can-be-assigned-to-branch',
                        'view-delivery-orders-page',
                        'filter-branch-content',
                        'manage-developer-settings',
                        'can-view-audit',

                        'view-any-branch',
                        'view-branch',
                        'create-branch',
                        'update-branch',
                        'delete-branch',
                        'mark-default-branch',
                        'unmark-default-branch',
                        'publish-branch',
                        'unpublish-branch',
                        'view-any-gift',
                        'view-gift',
                        'create-gift',
                        'update-gift',
                        'delete-gift',
                        'publish-gift',
                        'unpublish-gift',
                        'show-code-gift',
                        'view-any-permission',
                        'view-permission',
                        'create-permission',
                        'update-permission',
                        'delete-permission',
                        'view-any-payment-method',
                        'view-payment-method',
                        'create-payment-method',
                        'update-payment-method',
                        'delete-payment-method',
                    ]
                )
                ->pluck('code')->toArray(),
            Role::ROLE_DEVELOPER_CODE => Permission::all()
                ->whereNotIn(
                    'code',
                    [
                        'can-receive-order-notifications',
                        'can-be-assigned-to-take-orders',
                        'can-be-assigned-to-branch',
                        'view-delivery-orders-page',
                        'filter-branch-content',
                    ]
                )
                ->pluck('code')->toArray(),
            Role::ROLE_MANAGER_CODE => Permission::all()
                ->whereNotIn(
                    'code',
                    [
                        'can-be-assigned-to-take-orders',
                        'view-delivery-orders-page',
                        'manage-developer-settings',
                        'can-view-audit',
                        'view-dashboard',

                        'view-any-branch',
                        'view-branch',
                        'create-branch',
                        'update-branch',
                        'delete-branch',
                        'mark-default-branch',
                        'unmark-default-branch',
                        'publish-branch',
                        'unpublish-branch',
                        'view-any-gift',
                        'view-gift',
                        'create-gift',
                        'update-gift',
                        'delete-gift',
                        'publish-gift',
                        'unpublish-gift',
                        'show-code-gift',
                        'view-any-permission',
                        'view-permission',
                        'create-permission',
                        'update-permission',
                        'delete-permission',
                        'view-any-payment-method',
                        'view-payment-method',
                        'create-payment-method',
                        'update-payment-method',
                        'delete-payment-method',
                    ]
                )
                ->pluck('code')->toArray(),
            Role::ROLE_DELIVERY_CODE => Permission::all()
                ->whereIn(
                    'code',
                    [
                        'can-be-assigned-to-take-orders',
                        'can-be-assigned-to-branch',
                        'view-delivery-orders-page',
                        'finish-order',
                    ]
                )
                ->pluck('code')->toArray(),
            Role::ROLE_CUSTOMER_CODE => [
                // none
            ],
        ];
        foreach ($rolesPermissions as $roleName => $permissionNames) {
            $role = Role::where('code', $roleName)->first();
            $permissionIds = Permission::whereIn('code', $permissionNames)->pluck('id');
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }

        $this->command->info('seeding owner');
        User::insert([
            'name' => 'owner',
            'email' => 'owner@owner.com',
            'password' => Hash::make('owndc8rqy0f6vasybipb'),
            'role_id' => Role::where('code', '=', 'super-admin')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id'),
        ]);

        $this->command->info('seeding developer');
        User::insert([
            'name' => 'developer',
            'email' => 'developer@developer.com',
            'password' => Hash::make('bvyaer68r3pq68bvp86s'),
            'role_id' => Role::where('code', '=', 'developer')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id'),
        ]);

        $this->command->info('seeding managers');
        $managerPassword = Hash::make('mandc8rqy0f6vasybipb');
        User::insert([
            'name' => 'manager 1',
            'email' => 'manager1@manager1.com',
            'password' => $managerPassword,
            'role_id' => Role::where('code', '=', 'manager')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id'),
        ]);
        User::insert([
            'name' => 'manager 2',
            'email' => 'manager2@manager2.com',
            'password' => $managerPassword,
            'role_id' => Role::where('code', '=', 'manager')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Riyadh')->value('id'),
        ]);
        User::insert([
            'name' => 'manager 3',
            'email' => 'manager3@manager3.com',
            'password' => $managerPassword,
            'role_id' => Role::where('code', '=', 'manager')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Makkah')->value('id'),
        ]);

        $this->command->info('seeding delivery');
        $deliveryPassword = Hash::make('deldc8rqy0f6vasybipb');
        User::insert([
            'name' => 'delivery 1',
            'email' => 'delivery1@delivery1.com',
            'password' => $deliveryPassword,
            'role_id' => Role::where('code', '=', Role::ROLE_DELIVERY_CODE)->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id'),
        ]);
        User::insert([
            'name' => 'delivery 2',
            'email' => 'delivery2@delivery2.com',
            'password' => $deliveryPassword,
            'role_id' => Role::where('code', '=', Role::ROLE_DELIVERY_CODE)->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id'),
        ]);
        User::insert([
            'name' => 'delivery 3',
            'email' => 'delivery3@delivery3.com',
            'password' => $deliveryPassword,
            'role_id' => Role::where('code', '=', Role::ROLE_DELIVERY_CODE)->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Riyadh')->value('id'),
        ]);
        User::insert([
            'name' => 'delivery 4',
            'email' => 'delivery4@delivery4.com',
            'password' => $deliveryPassword,
            'role_id' => Role::where('code', '=', Role::ROLE_DELIVERY_CODE)->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Riyadh')->value('id'),
        ]);
        User::insert([
            'name' => 'delivery 5',
            'email' => 'delivery5@delivery5.com',
            'password' => $deliveryPassword,
            'role_id' => Role::where('code', '=', Role::ROLE_DELIVERY_CODE)->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Makkah')->value('id'),
        ]);
        User::insert([
            'name' => 'delivery 6',
            'email' => 'delivery6@delivery6.com',
            'password' => $deliveryPassword,
            'role_id' => Role::where('code', '=', Role::ROLE_DELIVERY_CODE)->value('id'),
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
        $branch = Branch::insert([
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Jeddah Branch', 'فرع جدة']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['Jeddah Branch', 'فرع جدة']), JSON_UNESCAPED_UNICODE),
                'latitude' => 21.5292,
                'longitude' => 39.1611,
                'is_default' => true,
                'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id'),
                'published_at' => now(),
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Riyadh Branch', 'فرع الرياض']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['Riyadh Branch', 'فرع الرياض']), JSON_UNESCAPED_UNICODE),
                'latitude' => 24.7136,
                'longitude' => 46.6753,
                'is_default' => true,
                'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Riyadh')->value('id'),
                'published_at' => now(),
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Makkah Branch', 'فرع مكة']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['Makkah Branch', 'فرع مكة']), JSON_UNESCAPED_UNICODE),
                'latitude' => 21.4241,
                'longitude' => 39.8173,
                'is_default' => true,
                'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Makkah')->value('id'),
                'published_at' => now(),
            ],
        ]);
        $branches = Branch::all();

        $this->command->info('seeding branches config');
        foreach ($branches as $branch) {
            foreach ($products as $product) {
                $minimumQuantity = fake()->numberBetween(1, 10);
                $branch->products()->attach($product->id, [
                    'price' => fake()->randomFloat(2, 0.2, 50),
                    'discount' => $this->faker->boolean(20) ? fake()->numberBetween(5, 30) : 0,
                    'minimum_order_quantity' => $minimumQuantity,
                    'maximum_order_quantity' => fake()->numberBetween($minimumQuantity + 10, 50),
                    'quantity' => fake()->numberBetween(100, 10000),
                    'expires_at' => fake()->dateTimeBetween('+20 days', '+1 year'),
                    'published_at' => $this->faker->boolean(70) ? Carbon::parse('2025-01-01 00:00:00') : null,
                ]);
            }
            Advertisement::factory()
                ->count($branchAdvertisementCount)
                ->for($branch)
                ->has(File::factory()->count($advertisementFileCount), 'files')
                ->create();
        }

        $this->command->info('seeding branches users');
        foreach (Branch::all() as $branch) {
            $managers = User::where('role_id', '=', Role::where('code', '=', 'manager')->value('id'))
                ->where('city_id', '=', $branch->city_id)
                ->get();
            foreach ($managers as $manager) {
                $branch->users()->attach($manager->id);
            }
            $deliveries = User::where('role_id', '=', Role::where('code', '=', Role::ROLE_DELIVERY_CODE)->value('id'))
                ->where('city_id', '=', $branch->city_id)
                ->get();
            foreach ($deliveries as $delivery) {
                $branch->users()->attach($delivery->id);
            }
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

        $this->command->info('seeding gifts');
        Gift::insert([
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['3 Riyal gift', 'هديه بقيمة 3 ريالات']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['3 Riyal gift', 'هديه بقيمة 3 ريالات']), JSON_UNESCAPED_UNICODE),
                'points' => '150',
                'code' => 'gift123',
                'discount' => '3',
                'allowed_sub_total_price' => '150',
                'published_at' => now(),
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['6 Riyal gift', 'هديه بقيمة 6 ريالات']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['6 Riyal gift', 'هديه بقيمة 6 ريالات']), JSON_UNESCAPED_UNICODE),
                'points' => '300',
                'code' => 'gift1234',
                'discount' => '6',
                'allowed_sub_total_price' => '300',
                'published_at' => now(),
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['10 Riyal gift', 'هديه بقيمة 10 ريالات']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['10 Riyal gift', 'هديه بقيمة 10 ريالات']), JSON_UNESCAPED_UNICODE),
                'points' => '500',
                'code' => 'gift12345',
                'discount' => '10',
                'allowed_sub_total_price' => '500',
                'published_at' => now(),
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['20 Riyal gift', 'هديه بقيمة 20 ريال']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['20 Riyal gift', 'هديه بقيمة 20 ريال']), JSON_UNESCAPED_UNICODE),
                'points' => '1000',
                'code' => 'gift123456',
                'discount' => '20',
                'allowed_sub_total_price' => '1000',
                'published_at' => now(),
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['30 Riyal gift', 'هديه بقيمة 30 ريال']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['30 Riyal gift', 'هديه بقيمة 30 ريالات']), JSON_UNESCAPED_UNICODE),
                'points' => '1500',
                'code' => 'gift1234567',
                'discount' => '30',
                'allowed_sub_total_price' => '1500',
                'published_at' => now(),
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['50 Riyal gift', 'هديه بقيمة 50 ريال']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['50 Riyal gift', 'هديه بقيمة 50 ريال']), JSON_UNESCAPED_UNICODE),
                'points' => '2500',
                'code' => 'gift12345678',
                'discount' => '50',
                'allowed_sub_total_price' => '2500',
                'published_at' => now(),
            ],
        ]);

        $this->command->info('seeding payment methods');
        foreach ($branches as $branch) {
            PaymentMethod::insert([
                [
                    'branch_id' => $branch->id,
                    'code' => PaymentMethod::PAY_ON_DELIVERY_CODE,
                    'title' => json_encode(Factory::translations(['en', 'ar'], ['Pay on Delivery', 'الدفع عند الاستلام']), JSON_UNESCAPED_UNICODE),
                    'published_at' => now(),
                    'order' => '1',
                ],
                [
                    'branch_id' => $branch->id,
                    'code' => 'apple-pay',
                    'title' => json_encode(Factory::translations(['en', 'ar'], ['Apple Pay', 'Apple Pay']), JSON_UNESCAPED_UNICODE),
                    'published_at' => null,
                    'order' => '2',
                ],
                [
                    'branch_id' => $branch->id,
                    'code' => 'google-pay',
                    'title' => json_encode(Factory::translations(['en', 'ar'], ['Google Pay', 'Google Pay']), JSON_UNESCAPED_UNICODE),
                    'published_at' => null,
                    'order' => '2',
                ],
                [
                    'branch_id' => $branch->id,
                    'code' => 'mada-pay',
                    'title' => json_encode(Factory::translations(['en', 'ar'], ['Mada Pay', 'Mada Pay']), JSON_UNESCAPED_UNICODE),
                    'published_at' => null,
                    'order' => '3',
                ],
                [
                    'branch_id' => $branch->id,
                    'code' => 'stc-pay',
                    'title' => json_encode(Factory::translations(['en', 'ar'], ['STC Pay', 'STC Pay']), JSON_UNESCAPED_UNICODE),
                    'published_at' => null,
                    'order' => '3',
                ],
            ]);
        }

        $this->command->info('seeding users');
        User::factory($userCount)
            ->has(UserAddress::factory()->count($userAddressCount), 'addresses')
            ->has(UserNotification::factory()->count($userNotificationCount), 'userNotifications')
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
                    'order_id' => $order->id,
                ]);
                $products = Product::inRandomOrder()
                    ->limit(rand(2, 10))
                    ->get();
                foreach ($products as $product) {
                    $branchProduct = BranchProduct::where('branch_id', $order->branch_id)
                        ->where('product_id', $product->id)
                        ->first();
                    CartProduct::factory()->create([
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'title' => $product->getTranslations('title'),
                        'price' => $branchProduct ? ($branchProduct->discount > 0 ? $branchProduct->price - ($branchProduct->price * ($branchProduct->discount / 100)) : $branchProduct->price) : 0,
                        'quantity' => rand(1, 5),
                    ]);
                }
                $subtotal = $cart->cartProducts->sum(
                    fn($cartProduct) => $cartProduct->price * $cartProduct->quantity
                );
                $totalPrice = $subtotal - $order->discount_price + $order->delivery_fee + $order->service_fee + $order->tax_amount;
                if ($order->order_status === OrderStatus::CLOSED && $order->isPayOnDelivery()) {
                    $order->update([
                        'subtotal_price' => $subtotal,
                        'total_price' => $totalPrice,
                        'payed_price' => $this->faker->boolean(90) ? $totalPrice : $totalPrice - rand(1, 5),
                    ]);
                } else {
                    $order->update([
                        'subtotal_price' => $subtotal,
                        'total_price' => $totalPrice,
                    ]);
                }
            });

        $this->command->info('Seeding advertisement users');
        $advertisements = Advertisement::all()->toArray();
        $users = User::all();
        $randomUsers = $users->random(rand(floor($users->count() * 0.3), floor($users->count() * 0.5)));
        foreach ($randomUsers as $user) {
            $randomAds = array_rand($advertisements, rand(1, 5));
            foreach ((array) $randomAds as $adIndex) {
                AdvertisementUser::firstOrCreate([
                    'user_id' => $user->id,
                    'advertisement_id' => $advertisements[$adIndex]['id'],
                ]);
            }
        }

        $this->command->info('Seeding feedback and tickets');
        $users = User::all();
        $randomUsers = $users->random(rand(floor($users->count() * 0.3), floor($users->count() * 0.5)));
        foreach ($randomUsers as $user) {
            foreach ((array) $randomAds as $adIndex) {
                $branchId = Branch::query()
                    ->where('city_id', $user->city_id)
                    ->where('is_default', true)
                    ->value('id');
                Feedback::create([
                    'user_id' => $user->id,
                    'feedback' => $this->faker->sentence(),
                    'branch_id' => $branchId,
                ]);
            }
        }
        foreach ($randomUsers as $user) {
            foreach ((array) $randomAds as $adIndex) {
                $branchId = Branch::query()
                    ->where('city_id', $user->city_id)
                    ->where('is_default', true)
                    ->value('id');
                Ticket::create([
                    'user_id' => $user->id,
                    'question' => $this->faker->sentence(),
                    'branch_id' => $branchId,
                ]);
            }
        }
    }
}
