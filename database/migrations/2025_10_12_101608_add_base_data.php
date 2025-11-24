<?php

use App\Models\Branch;
use App\Models\City;
use App\Models\Gift;
use App\Models\PaymentMethod;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionsData;
use Database\Seeders\SettingsData;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        SettingsData::run();

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

        PermissionsData::run();

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

        User::insert([
            'name' => 'owner',
            'email' => 'owner@owner.com',
            'password' => Hash::make('owndc8rqy0f6vasybipb'),
            'role_id' => Role::where('code', '=', 'super-admin')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id'),
        ]);

        User::insert([
            'name' => 'developer',
            'email' => 'developer@developer.com',
            'password' => Hash::make('bvyaer68r3pq68bvp86s'),
            'role_id' => Role::where('code', '=', 'developer')->value('id'),
            'city_id' => City::whereJsonContainsLocales('name', ['en'], 'Jeddah')->value('id'),
        ]);

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

        Gift::insert([
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['3 Riyal gift', 'هديه بقيمة 3 ريالات']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['3 Riyal gift', 'هديه بقيمة 3 ريالات']), JSON_UNESCAPED_UNICODE),
                'points' => '150',
                'code' => 'gift688180',
                'discount' => '3',
                'allowed_sub_total_price' => '150',
                'published_at' => now(),
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['6 Riyal gift', 'هديه بقيمة 6 ريالات']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['6 Riyal gift', 'هديه بقيمة 6 ريالات']), JSON_UNESCAPED_UNICODE),
                'points' => '300',
                'code' => 'gift391511',
                'discount' => '6',
                'allowed_sub_total_price' => '300',
                'published_at' => now(),
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['10 Riyal gift', 'هديه بقيمة 10 ريالات']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['10 Riyal gift', 'هديه بقيمة 10 ريالات']), JSON_UNESCAPED_UNICODE),
                'points' => '500',
                'code' => 'gift729178',
                'discount' => '10',
                'allowed_sub_total_price' => '500',
                'published_at' => now(),
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['20 Riyal gift', 'هديه بقيمة 20 ريال']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['20 Riyal gift', 'هديه بقيمة 20 ريال']), JSON_UNESCAPED_UNICODE),
                'points' => '1000',
                'code' => 'gift166240',
                'discount' => '20',
                'allowed_sub_total_price' => '1000',
                'published_at' => now(),
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['30 Riyal gift', 'هديه بقيمة 30 ريال']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['30 Riyal gift', 'هديه بقيمة 30 ريالات']), JSON_UNESCAPED_UNICODE),
                'points' => '1500',
                'code' => 'gift442532',
                'discount' => '30',
                'allowed_sub_total_price' => '1500',
                'published_at' => now(),
            ],
            [
                'title' => json_encode(Factory::translations(['en', 'ar'], ['50 Riyal gift', 'هديه بقيمة 50 ريال']), JSON_UNESCAPED_UNICODE),
                'description' => json_encode(Factory::translations(['en', 'ar'], ['50 Riyal gift', 'هديه بقيمة 50 ريال']), JSON_UNESCAPED_UNICODE),
                'points' => '2500',
                'code' => 'gift108296',
                'discount' => '50',
                'allowed_sub_total_price' => '2500',
                'published_at' => now(),
            ],
        ]);

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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
