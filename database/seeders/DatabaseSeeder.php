<?php

namespace Database\Seeders;

class DatabaseSeeder extends BaseSeeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->generateData(
            rootCategoryCount: 3,
            productCount: 200,
            productCategoryCount: 10,
            branchAdvertisementCount: 20,
            userCount: 100,
            userAddressCount: 3,
            userNotificationCount: 3,
            favoriteCount: 3,
            packageProductCount: 10,
            productFileCount: 3,
            categoryFileCount: 1,
            advertisementFileCount: 1,
            orderCount: 500
        );
    }
}
