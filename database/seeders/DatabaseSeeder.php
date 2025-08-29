<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Enums\SettingType;
use App\Models\Advertisement;
use App\Models\Branch;
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

class DatabaseSeeder extends BaseSeeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $rootCategoryCount = 3;
        $productCount = 200;
        $productCategoryCount = 10;
        $branchCount = 3;
        $branchAdvertisementCount = 30;
        $userCount = 100;
        $userAddressCount = 3;
        $userNotificationCount = 3;
        $favoriteCount = 3;
        $packageProductCount = 10;
        $productFileCount = 3;
        $categoryFileCount = 1;
        $advertisementFileCount = 1;
        $orderCount = 200;

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
            ['name' => json_encode(Factory::translations(['en', 'ar'], ['Jeddah', 'جدة']), JSON_UNESCAPED_UNICODE)],
            ['name' => json_encode(Factory::translations(['en', 'ar'], ['Riyadh', 'رياض']), JSON_UNESCAPED_UNICODE)],
            ['name' => json_encode(Factory::translations(['en', 'ar'], ['Makkah', 'مكة']), JSON_UNESCAPED_UNICODE)],
        ]);

        $this->command->info('seeding roles');
        Role::insert([
            ['title' => 'admin'],
            ['title' => 'user'],
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

        $this->command->info('seeding admin user');
        User::insert([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('dc8rqy0f6vasybipb'),
            'role_id' => Role::where('title', '=', 'admin')->value('id'),
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

        $cities = City::all();
        $citySequence = [];
        foreach ($cities as $city) {
            $citySequence[] = ['city_id' => $city->id];
        }

        $this->command->info('seeding branches');
        Branch::factory()
            ->sequence(...$citySequence)
            ->count($branchCount)
            ->hasAttached(
                $products,
                function () {
                    $minimumQuantity = $this->faker->numberBetween(1, 10);
                    $maximumQuantity = $this->faker->numberBetween($minimumQuantity + 10, 50);

                    return [
                        'price' => $this->faker->randomFloat(2, 10, 500),
                        'discount' => $this->faker->numberBetween(0, 50),
                        'maximum_order_quantity' => $maximumQuantity,
                        'minimum_order_quantity' => $minimumQuantity,
                        'quantity' => $this->faker->numberBetween(100, 10000),
                        'expires_at' => $this->faker->dateTimeBetween('+10 days', '+1 year'),
                        'published_at' => $this->faker->dateTimeBetween('-1 year', '-10 days'),
                    ];
                }
            )
            ->has(
                Advertisement::Factory()
                    ->has(File::factory()->count($advertisementFileCount), 'files')
                    ->count($branchAdvertisementCount)
            )
            ->create();

        $branches = Branch::all();

        $this->command->info('seeding coupons');
        foreach ($branches as $branch) {
            Coupon::insert([
                [
                    'title' => json_encode(Factory::translations(['en', 'ar'], ['test coupon', 'كوبون تجريبي']), JSON_UNESCAPED_UNICODE),
                    'description' => json_encode(Factory::translations(['en', 'ar'], ['test coupon', 'كوبون تجريبي']), JSON_UNESCAPED_UNICODE),
                    'code' => '123456',
                    'type' => CouponType::TIMED->value,
                    'config' => '{"value":15,"start_date":"2025-06-01","end_date":"2026-06-30","use_limit":5,"user_limit":100}',
                    'branch_id' => $branch->id,
                ],
                [
                    'title' => json_encode(Factory::translations(['en', 'ar'], ['test coupon', 'كوبون تجريبي']), JSON_UNESCAPED_UNICODE),
                    'description' => json_encode(Factory::translations(['en', 'ar'], ['test coupon', 'كوبون تجريبي']), JSON_UNESCAPED_UNICODE),
                    'code' => '123456',
                    'type' => CouponType::TIMED->value,
                    'config' => '{"value":10,"start_date":"2025-09-01","end_date":"2026-06-30","use_limit":3,"user_limit":50}',
                    'branch_id' => $branch->id,
                ],
                [
                    'title' => json_encode(Factory::translations(['en', 'ar'], ['test coupon', 'كوبون تجريبي']), JSON_UNESCAPED_UNICODE),
                    'description' => json_encode(Factory::translations(['en', 'ar'], ['test coupon', 'كوبون تجريبي']), JSON_UNESCAPED_UNICODE),
                    'code' => '123456',
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
                FavoriteFactory::new()
                    ->count($favoriteCount)
                    ->sequence(fn($seq) => [
                        'product_id' => $products[$seq->index % count($products)]->id,
                    ]),
                'favorites'
            )
            ->has(PackageFactory::new()->hasAttached($products->random(rand(3, 7))->values())->count($packageProductCount), 'packages')
            ->create();

        $this->command->info('seeding orders');
        Order::factory()->count($orderCount)->create();
    }
}
