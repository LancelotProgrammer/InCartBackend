<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Enums\SettingType;
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
use App\Models\PaymentMethod;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
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
        $settings = [
            // Service
            [
                'key' => 'is_system_online',
                'value' => '1',
                'type' => SettingType::BOOL,
                'group' => 'service',
                'is_locked' => false,
            ],

            // Social Media
            [
                'key' => 'whatsapp',
                'value' => 'http://localhost:8000/admin/settings',
                'type' => SettingType::STR,
                'group' => 'social',
                'is_locked' => false,
            ],
            [
                'key' => 'telegram',
                'value' => 'http://localhost:8000/admin/settings',
                'type' => SettingType::STR,
                'group' => 'social',
                'is_locked' => false,
            ],
            [
                'key' => 'facebook',
                'value' => 'http://localhost:8000/admin/settings',
                'type' => SettingType::STR,
                'group' => 'social',
                'is_locked' => false,
            ],

            // Order Config
            [
                'key' => 'service_fee',
                'value' => '1',
                'type' => SettingType::FLOAT,
                'group' => 'order',
                'is_locked' => false,
            ],
            [
                'key' => 'tax_rate',
                'value' => '1',
                'type' => SettingType::FLOAT,
                'group' => 'order',
                'is_locked' => false,
            ],
            [
                'key' => 'min_distance',
                'value' => '0',
                'type' => SettingType::FLOAT,
                'group' => 'order',
                'is_locked' => false,
            ],
            [
                'key' => 'max_distance',
                'value' => '100',
                'type' => SettingType::FLOAT,
                'group' => 'order',
                'is_locked' => false,
            ],
            [
                'key' => 'price_per_kilometer',
                'value' => '2',
                'type' => SettingType::FLOAT,
                'group' => 'order',
                'is_locked' => false,
            ],

            // Legal
            [
                'key' => 'privacy_policy',
                'value' => 'privacy_policy',
                'type' => SettingType::STR,
                'group' => 'legal',
                'is_locked' => false,
            ],
            [
                'key' => 'terms_of_services',
                'value' => 'terms_of_services',
                'type' => SettingType::STR,
                'group' => 'legal',
                'is_locked' => false,
            ],
            [
                'key' => 'faqs',
                'value' => 'faqs',
                'type' => SettingType::STR,
                'group' => 'legal',
                'is_locked' => false,
            ],

            // support
            [
                'key' => 'allowed_ticket_count',
                'value' => '5',
                'type' => SettingType::INT,
                'group' => 'support',
                'is_locked' => false,
            ],
            [
                'key' => 'allowed_feedback_count',
                'value' => '5',
                'type' => SettingType::INT,
                'group' => 'support',
                'is_locked' => false,
            ],
        ];
        Setting::insert($settings);

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
        Permission::insert([
            // crud
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Advertisement', 'عرض أي إعلان']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-advertisement',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Advertisement', 'عرض تفاصيل الإعلان']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-advertisement',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Advertisement', 'إنشاء إعلان']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-advertisement',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Advertisement', 'تحديث الإعلان']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-advertisement',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Advertisement', 'حذف الإعلان']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-advertisement',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Branch', 'عرض أي فرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Branch', 'عرض تفاصيل الفرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Branch', 'إنشاء فرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Branch', 'تحديث الفرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Branch', 'حذف الفرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Category', 'عرض أي فئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Category', 'عرض تفاصيل الفئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Category', 'إنشاء فئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Category', 'تحديث الفئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Category', 'حذف الفئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any City', 'عرض أي مدينة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-city',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View City', 'عرض تفاصيل المدينة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-city',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create City', 'إنشاء مدينة']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-city',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update City', 'تحديث المدينة']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-city',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete City', 'حذف المدينة']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-city',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Coupon', 'عرض أي كوبون']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-coupon',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Coupon', 'عرض تفاصيل الكوبون']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-coupon',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Coupon', 'إنشاء كوبون']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-coupon',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Coupon', 'تحديث الكوبون']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-coupon',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Coupon', 'حذف الكوبون']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-coupon',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Feedback', 'عرض أي ملاحظة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-feedback',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Feedback', 'عرض تفاصيل الملاحظة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-feedback',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Feedback', 'إنشاء ملاحظة']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-feedback',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Feedback', 'تحديث الملاحظة']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-feedback',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Feedback', 'حذف الملاحظة']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-feedback',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Order', 'عرض أي طلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Order', 'عرض تفاصيل الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Order', 'إنشاء طلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Order', 'تحديث الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Order', 'حذف الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Order Archive', 'عرض أي أرشيف طلبات']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-order-archive',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Order Archive', 'عرض تفاصيل أرشيف الطلبات']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-order-archive',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Order Archive', 'إنشاء أرشيف طلبات']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-order-archive',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Order Archive', 'تحديث أرشيف الطلبات']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-order-archive',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Order Archive', 'حذف أرشيف الطلبات']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-order-archive',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Payment Method', 'عرض أي طريقة دفع']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-payment-method',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Payment Method', 'عرض تفاصيل طريقة الدفع']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-payment-method',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Payment Method', 'إنشاء طريقة دفع']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-payment-method',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Payment Method', 'تحديث طريقة الدفع']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-payment-method',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Payment Method', 'حذف طريقة الدفع']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-payment-method',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Permission', 'عرض أي صلاحية']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-permission',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Permission', 'عرض تفاصيل الصلاحية']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-permission',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Permission', 'إنشاء صلاحية']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-permission',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Permission', 'تحديث الصلاحية']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-permission',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Permission', 'حذف الصلاحية']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-permission',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Product', 'عرض أي منتج']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-product',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Product', 'عرض تفاصيل المنتج']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-product',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Product', 'إنشاء منتج']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-product',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Product', 'تحديث المنتج']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-product',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Product', 'حذف المنتج']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-product',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Role', 'عرض أي دور']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-role',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Role', 'عرض تفاصيل الدور']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-role',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Role', 'إنشاء دور']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-role',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Role', 'تحديث الدور']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-role',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Role', 'حذف الدور']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-role',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Setting', 'عرض أي إعداد']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-setting',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Setting', 'عرض تفاصيل الإعداد']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-setting',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Setting', 'إنشاء إعداد']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-setting',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Setting', 'تحديث الإعداد']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-setting',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Setting', 'حذف الإعداد']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-setting',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any Ticket', 'عرض أي تذكرة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-ticket',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Ticket', 'عرض تفاصيل التذكرة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-ticket',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create Ticket', 'إنشاء تذكرة']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-ticket',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update Ticket', 'تحديث التذكرة']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-ticket',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete Ticket', 'حذف التذكرة']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-ticket',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Any User', 'عرض أي مستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-any-user',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View User', 'عرض تفاصيل المستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-user',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Create User', 'إنشاء مستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'create-user',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Update User', 'تحديث المستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'update-user',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Delete User', 'حذف المستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'delete-user',
            ],

            // custom
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Can Receive Order Notifications', 'يمكنه استلام إشعارات الطلبات']), JSON_UNESCAPED_UNICODE),
                'code' => 'can-receive-order-notifications',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Can Be Assigned To Take Orders', 'يمكن تعيينه لأخذ الطلبات']), JSON_UNESCAPED_UNICODE),
                'code' => 'can-be-assigned-to-take-orders',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Can Be Assigned To Branch', 'يمكن تعيينه للفرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'can-be-assigned-to-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Dashboard', 'عرض لوحة التحكم']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-dashboard',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Manage Settings', 'إدارة الإعدادات']), JSON_UNESCAPED_UNICODE),
                'code' => 'manage-settings',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Manage Developer Settings', 'إدارة إعدادات المطور']), JSON_UNESCAPED_UNICODE),
                'code' => 'manage-developer-settings',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Mark Default Branch', 'تعيين فرع افتراضي']), JSON_UNESCAPED_UNICODE),
                'code' => 'mark-default-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unmark Default Branch', 'إلغاء تعيين فرع افتراضي']), JSON_UNESCAPED_UNICODE),
                'code' => 'unmark-default-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Publish Branch', 'نشر الفرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'publish-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unpublish Branch', 'إلغاء نشر الفرع']), JSON_UNESCAPED_UNICODE),
                'code' => 'unpublish-branch',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Publish Advertisement', 'نشر الإعلان']), JSON_UNESCAPED_UNICODE),
                'code' => 'publish-advertisement',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unpublish Advertisement', 'إلغاء نشر الإعلان']), JSON_UNESCAPED_UNICODE),
                'code' => 'unpublish-advertisement',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Publish Payment Method', 'نشر طريقة الدفع']), JSON_UNESCAPED_UNICODE),
                'code' => 'publish-payment-method',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unpublish Payment Method', 'إلغاء نشر طريقة الدفع']), JSON_UNESCAPED_UNICODE),
                'code' => 'unpublish-payment-method',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Show Code Coupon', 'عرض كود الكوبون']), JSON_UNESCAPED_UNICODE),
                'code' => 'show-code-coupon',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Publish Product', 'نشر المنتج']), JSON_UNESCAPED_UNICODE),
                'code' => 'publish-product',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Publish Coupon', 'نشر الكوبون']), JSON_UNESCAPED_UNICODE),
                'code' => 'publish-coupon',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unpublish Coupon', 'إلغاء نشر الكوبون']), JSON_UNESCAPED_UNICODE),
                'code' => 'unpublish-coupon',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Publish Category', 'نشر الفئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'publish-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unpublish Category', 'إلغاء نشر الفئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'unpublish-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Products Category', 'عرض منتجات الفئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-products-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Categories Category', 'عرض فئات الفئة']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-categories-category',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Cancel Order', 'إلغاء الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'cancel-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Approve Order', 'الموافقة على الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'approve-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Select Delivery Order', 'اختيار توصيل الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'select-delivery-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Finish Order', 'إنهاء الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'finish-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Archive Order', 'أرشفة الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'archive-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['View Invoice Order', 'عرض فاتورة الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'view-invoice-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Audit Order', 'مراجعة سجل الطلب']), JSON_UNESCAPED_UNICODE),
                'code' => 'audit-order',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Block User', 'حظر المستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'block-user',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unblock User', 'إلغاء حظر المستخدم']), JSON_UNESCAPED_UNICODE),
                'code' => 'unblock-user',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Mark Important Feedback', 'تعيين ملاحظة كمهمة']), JSON_UNESCAPED_UNICODE),
                'code' => 'mark-important-feedback',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unmark Important Feedback', 'إلغاء تعيين ملاحظة كمهمة']), JSON_UNESCAPED_UNICODE),
                'code' => 'unmark-important-feedback',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Process Feedback', 'معالجة الملاحظة']), JSON_UNESCAPED_UNICODE),
                'code' => 'process-feedback',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Mark Important Ticket', 'تعيين تذكرة كمهمة']), JSON_UNESCAPED_UNICODE),
                'code' => 'mark-important-ticket',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Unmark Important Ticket', 'إلغاء تعيين تذكرة كمهمة']), JSON_UNESCAPED_UNICODE),
                'code' => 'unmark-important-ticket',
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['Process Ticket', 'معالجة التذكرة']), JSON_UNESCAPED_UNICODE),
                'code' => 'process-ticket',
            ],
        ]);

        $this->command->info('seeding roles permissions');
        $rolesPermissions = [
            Role::ROLE_SUPER_ADMIN_CODE => Permission::all()
                ->whereNotIn(
                    'code',
                    [
                        'can-receive-order-notifications',
                        'can-be-assigned-to-take-orders',
                        'can-be-assigned-to-branch',
                        'manage-developer-settings',
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
                    ]
                )
                ->pluck('code')->toArray(),
            Role::ROLE_MANAGER_CODE => Permission::all()
                ->whereNotIn(
                    'code',
                    [
                        'can-be-assigned-to-take-orders',
                        'manage-developer-settings',
                    ]
                )
                ->pluck('code')->toArray(),
            Role::ROLE_DELIVERY_CODE => Permission::all()
                ->whereIn(
                    'code',
                    [
                        'can-be-assigned-to-take-orders',
                        'can-be-assigned-to-branch',
                        'view-any-order',
                        'view-order',
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
                'title' => json_encode(Factory::translations(['en', 'ar'], ['branch number 1', 'الفرع رقم 1']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['branch number 1', 'الفرع رقم 1']), JSON_UNESCAPED_UNICODE),
                'latitude' => 21.5292,
                'longitude' => 39.1611,
                'is_default' => true,
                'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id'),
                'published_at' => now(),
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['branch number 2', 'الفرع رقم 2']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['branch number 2', 'الفرع رقم 2']), JSON_UNESCAPED_UNICODE),
                'latitude' => 24.7136,
                'longitude' => 46.6753,
                'is_default' => true,
                'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Riyadh')->value('id'),
                'published_at' => now(),
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['branch number 3', 'الفرع رقم 3']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['branch number 3', 'الفرع رقم 3']), JSON_UNESCAPED_UNICODE),
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
                $maximumQuantity = fake()->numberBetween($minimumQuantity + 10, 50);
                $branch->products()->attach($product->id, [
                    'price' => fake()->randomFloat(2, 10, 500),
                    'discount' => $this->faker->boolean(20) ? fake()->numberBetween(5, 30) : 0,
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
            ->has(UserNotification::factory()->count($userNotificationCount), 'userNotifications')
            ->has(
                FavoriteFactory::new()->count($favoriteCount)->sequence(fn ($seq) => ['product_id' => $products[$seq->index % count($products)]->id]),
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
                        'price' => $branchProduct
                            ? ($branchProduct->discount > 0
                                ? $branchProduct->price - ($branchProduct->price * ($branchProduct->discount / 100))
                                : $branchProduct->price)
                            : 0,
                        'quantity' => rand(1, 5),
                    ]);
                }
                $subtotal = $cart->cartProducts->sum(
                    fn ($cartProduct) => $cartProduct->price * $cartProduct->quantity
                );
                $order->update([
                    'subtotal_price' => $subtotal,
                    'total_price' => $subtotal
                        - $order->coupon_discount
                        + $order->delivery_fee
                        + $order->service_fee
                        + $order->tax_amount,
                ]);
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
                Feedback::create([
                    'user_id' => $user->id,
                    'feedback' => $this->faker->sentence(),
                ]);
            }
        }
        foreach ($randomUsers as $user) {
            foreach ((array) $randomAds as $adIndex) {
                Ticket::create([
                    'user_id' => $user->id,
                    'question' => $this->faker->sentence(),
                ]);
            }
        }
    }
}
