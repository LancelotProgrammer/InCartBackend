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
        $this->generateData(
            rootCategoryCount: 3,
            productCount: 20,
            productCategoryCount: 10,
            branchAdvertisementCount: 5,
            userCount: 20,
            userAddressCount: 3,
            userNotificationCount: 3,
            favoriteCount: 3,
            packageProductCount: 3,
            productFileCount: 3,
            categoryFileCount: 1,
            advertisementFileCount: 1,
            orderCount: 20
        );
    }
}
