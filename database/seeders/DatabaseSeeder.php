<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Enums\SettingType;
use App\Enums\UnitType;
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
        $branchCount = 3;
        $branchAdvertisementCount = 10;
        $userCount = 100;
        $userAddressCount = 3;
        $userNotificationCount = 3;
        $favoriteCount = 3;
        $packageProductCount = 10;
        $productFileCount = 3;
        $advertisementFileCount = 1;
        $orderCount = 200;

        $this->command->info(PHP_EOL);
        $this->command->info('seeding static data');

        $this->command->info('seeding coupons');
        Coupon::insert(
            [
                'name' => 'test coupon',
                'description' => 'test coupon',
                'code' => '123456',
                'type' => CouponType::FIXED->value,
                'config' => '{"discount_amount":15,"start_date":"2025-06-01","end_date":"2026-06-30","use_limit":5,"user_limit":100}',
            ],
            [
                'name' => 'test coupon',
                'description' => 'test coupon',
                'code' => '123456',
                'type' => CouponType::FIXED->value,
                'config' => '{"discount_amount":10,"start_date":"2025-09-01","end_date":"2026-06-30","use_limit":3,"user_limit":50}',
            ],
            [
                'name' => 'test coupon',
                'description' => 'test coupon',
                'code' => '123456',
                'type' => CouponType::FIXED->value,
                'config' => '{"discount_amount":5,"start_date":"2025-10-01","end_date":"2026-06-30","use_limit":2,"user_limit":25}',
            ]
        );

        $this->command->info('seeding payment methods');
        PaymentMethod::insert(
            [
                'title' => 'Pay on Delivery',
                'order' => '1',
            ],
            [
                'title' => 'Apple Pay',
                'order' => '2',
            ],
            [
                'title' => 'Google Pay',
                'order' => '3',
            ],
            [
                'title' => 'Mada Pay',
                'order' => '3',
            ],
            [
                'title' => 'STC Pay',
                'order' => '3',
            ],
            [
                'title' => 'Credit Card',
                'order' => '3',
            ],
        );

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
            ['name' => 'Jeddah'],
            ['name' => 'Riyadh'],
            ['name' => 'Makkah'],
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
            'password' => Hash::make('admin123'),
            'role_id' => Role::where('title', '=', 'admin')->value('id'),
            'city_id' => City::where('name', '=', 'Makkah')->value('id'),
        ]);

        $this->command->info('seeding categories');
        RootCategoryFactory::new()->count($rootCategoryCount)->create();
        $secondLevelCategories = SecondLevelCategoryFactory::new()->count($rootCategoryCount * 5)->create();
        $thirdLevelCategories = ThirdLevelCategoryFactory::new()->count($rootCategoryCount * 5 * 2)->create();
        $allCategoriesToAttach = $secondLevelCategories->concat($thirdLevelCategories);

        $this->command->info('seeding products');
        $products = Product::factory()
            ->hasAttached($allCategoriesToAttach)
            ->has(File::factory()->count($productFileCount), 'files')
            ->count($productCount)->create();

        $this->command->info('seeding branches');
        $cities = City::all();
        $citySequence = [];
        foreach ($cities as $city) {
            $citySequence[] = ['city_id' => $city->id];
        }

        Branch::factory()
            ->sequence(...$citySequence)
            ->count($branchCount)
            ->hasAttached(
                $products,
                function () {
                    $minimumQuantity = $this->faker->numberBetween(1, 10);
                    $maximumQuantity = $this->faker->numberBetween($minimumQuantity + 10, 50);
                    $type = $this->faker->randomElement(UnitType::cases());

                    return [
                        'price' => $this->faker->randomFloat(2, 10, 500),
                        'unit' => $type->value,
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

        $this->command->info('seeding users');
        User::factory($userCount)
            ->has(UserAddress::factory()->count($userAddressCount), 'addresses')
            ->has(UserNotification::factory()->count($userNotificationCount), 'notifications')
            ->has(
                FavoriteFactory::new()
                    ->count($favoriteCount)
                    ->sequence(fn ($seq) => [
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
