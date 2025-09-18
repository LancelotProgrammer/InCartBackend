<?php

namespace Database\Seeders;

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
