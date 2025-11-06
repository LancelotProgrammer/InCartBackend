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
use App\Models\Order;
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

        $this->command->info('seeding branches config');
        $branches = Branch::all();
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
