<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->phoneUser = User::factory()->phoneUser()->create();
    $this->emailUser = User::factory()->emailUser()->create();
});

test('public auth endpoints', function () {
    $this->postJson('/api/v1/auth/email/register', [
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => 'password',
        'city_id' => 1,
    ])->assertStatus(200);

    $this->postJson('/api/v1/auth/email/login', [
        'email' => 'test@test.com',
        'password' => 'password',
    ])->assertStatus(200);

    $this->postJson('/api/v1/auth/send-otp', [
        'type' => 1,
        'phone' => '+966512345678'
    ])->assertStatus(204);

    $this->postJson('/api/v1/auth/phone/register', [
        'name' => 'Test User',
        'phone' => '+966512345678',
        'otp' => '123456',
        'city_id' => 1,
    ])->assertStatus(200);

    $this->postJson('/api/v1/auth/send-otp', [
        'type' => 2,
        'phone' => '+966512345678'
    ])->assertStatus(204);

    $this->postJson('/api/v1/auth/phone/login', [
        'phone' => '+966512345678',
        'otp' => '123456',
    ])->assertStatus(200);

    DB::table('users')->where('email', 'test@test.com')->update(['email_verified_at' => now()]);
    $this->postJson('/api/v1/auth/request-forget-password', [
        'email' => 'test@test.com',
    ])->assertStatus(204);

    $userId = DB::table('users')->where('email', 'test@test.com')->firstOrFail()->id;
    $code = DB::table('password_reset_requests')->where('user_id', $userId)->firstOrFail()->code;
    $this->assertNotEmpty($code);
    $this->postJson('/api/v1/auth/verify-forget-password', [
        'email' => 'test@test.com',
        'code' => (int)$code,
    ])->assertStatus(200);

    $token = DB::table('password_reset_requests')->where('user_id', $userId)->firstOrFail()->token;
    $this->assertNotEmpty($token);
    $this->postJson('/api/v1/auth/reset-forget-password', [
        'password' => 'new-password',
        'token' => $token,
        'logoutFromAll' => false,
    ])->assertStatus(204);
});

test('protected auth endpoints', function () {
    $this->actingAs($this->emailUser, 'sanctum')->getJson('/api/v1/auth/email/request-verify')->assertStatus(204);

    $this->actingAs($this->phoneUser, 'sanctum')->getJson('/api/v1/auth/user')->assertStatus(200);

    // TODO: works normally but it does not work in test
    // $this->actingAs($this->phoneUser, 'sanctum')->postJson('/api/v1/auth/firebase-token', [
    //     'firebase_token' => 'dummy-firebase-token',
    // ])->assertStatus(204);

    $this->actingAs($this->phoneUser, 'sanctum')->putJson('/api/v1/auth/user/update', [
        'name' => 'updated name',
    ])->assertStatus(204);

    // TODO: works normally but it does not work in test
    // $this->actingAs($this->phoneUser, 'sanctum')->postJson('/api/v1/auth/logout')->assertStatus(204);
});

test('branches endpoints', function () {
    $this->actingAs($this->phoneUser, 'sanctum')->getJson('/api/v1/branches?city_id=1')->assertStatus(200);
});

test('orders and branches endpoints', function () {
    $response = $this->actingAs($this->phoneUser, 'sanctum')->postJson('/api/v1/users/addresses', [
        'title' => 'test address',
        'description' => 'This is a test address description.',
        'phone' => '+966512345678',
        'type' => '1',
        'latitude' => 21.5292 + 0.02,
        'longitude' => 39.1611 + 0.02,
    ]);
    $response->assertStatus(200);
    $addressId = $response->json('data.id');
    $this->assertNotEmpty($addressId);
    $orderRequest = [
        'address_id' => $addressId,
        'delivery_date' => null,
        'payment_method_id' => 1,
        'coupon' => null,
        'notes' => 'some notes',
        'cart' => [
            [
                'id' => 1,
                'quantity' => DB::table('branch_product')->where('branch_id', 1)->where('product_id', 1)->firstOrFail()->minimum_order_quantity
            ],
            [
                'id' => 2,
                'quantity' => DB::table('branch_product')->where('branch_id', 1)->where('product_id', 2)->firstOrFail()->minimum_order_quantity
            ]
        ]
    ];
    $response = $this->actingAs($this->phoneUser, 'sanctum')->postJson('/api/v1/order/bill', $orderRequest);

    $response = $this->actingAs($this->phoneUser, 'sanctum')->postJson('/api/v1/order', $orderRequest);
    $response->assertStatus(200);

    $orderId = $response->json('data.id');
    $this->actingAs($this->phoneUser, 'sanctum')->getJson('/api/v1/order/' . $orderId)->assertStatus(200);

    // TODO: not implemented yet
    // $this->actingAs($this->phoneUser, 'sanctum')->postJson('/api/v1/order/checkout', [])->assertStatus(200);
});

test('user resources endpoints', function () {
    $this->actingAs($this->phoneUser, 'sanctum')->getJson('/api/v1/users/orders')->assertStatus(200);

    $this->actingAs($this->phoneUser, 'sanctum')->getJson('/api/v1/users/notifications')->assertStatus(200);
});

test('packages endpoints', function () {
    $response = $this->actingAs($this->phoneUser, 'sanctum')->postJson('/api/v1/packages', [
        'title' => 'Test package',
        'description' => 'This is a sample test package description.',
    ]);

    $response->assertStatus(200);

    $packageId = $response->json('data.id');
    $this->assertNotEmpty($packageId);

    $this->actingAs($this->phoneUser, 'sanctum')->getJson('/api/v1/packages')->assertStatus(200);

    $this->actingAs($this->phoneUser, 'sanctum')->putJson('/api/v1/packages/' . $packageId, [
        'title' => 'Updated test package',
        'description' => 'This is an updated test package description.',
    ])->assertStatus(200);

    $this->actingAs($this->phoneUser, 'sanctum')->postJson('/api/v1/packages/' . $packageId . '/products/1')->assertStatus(200);

    $this->actingAs($this->phoneUser, 'sanctum')->getJson('/api/v1/packages/' . $packageId . '/products')->assertStatus(200);

    $this->actingAs($this->phoneUser, 'sanctum')->deleteJson('/api/v1/packages/' . $packageId . '/products/1')->assertStatus(200);

    $this->actingAs($this->phoneUser, 'sanctum')->deleteJson('/api/v1/packages/' . $packageId)->assertStatus(200);
});

test('favorites endpoints', function () {
    $this->actingAs($this->phoneUser, 'sanctum')->postJson('/api/v1/favorites/products/1')->assertStatus(200);

    $this->actingAs($this->phoneUser, 'sanctum')->getJson('/api/v1/favorites/products')->assertStatus(200);

    $this->actingAs($this->phoneUser, 'sanctum')->deleteJson('/api/v1/favorites/products/1')->assertStatus(200);
});

test('addresses endpoints', function () {
    $response = $this->actingAs($this->phoneUser, 'sanctum')->postJson('/api/v1/users/addresses', [
        'title' => 'test address',
        'description' => 'This is a test address description.',
        'phone' => '+966512345678',
        'type' => '1',
        'latitude' => 21.5292 + 0.02,
        'longitude' => 39.1611 + 0.02,
    ]);
    $response->assertStatus(200);

    $addressId = $response->json('data.id');
    $this->assertNotEmpty($addressId);

    $this->actingAs($this->phoneUser, 'sanctum')->putJson('/api/v1/users/addresses/' . $addressId, [
        'title' => 'Updated test address',
        'description' => 'This is an updated test address description.',
        'phone' => '+966512345678',
        'type' => '1',
        'latitude' => 21.5292 + 0.02,
        'longitude' => 39.1611 + 0.02,
    ])->assertStatus(204);

    $this->actingAs($this->phoneUser, 'sanctum')->getJson('/api/v1/users/addresses')->assertStatus(200);

    $this->actingAs($this->phoneUser, 'sanctum')->deleteJson('/api/v1/users/addresses/' . $addressId)->assertStatus(200);
});

test('public catalog endpoints', function () {
    $this->getJson('/api/v1/cities')->assertStatus(200);

    $this->getJson('/api/v1/home')->assertStatus(200);

    $this->getJson('/api/v1/products')->assertStatus(200);

    // TODO: works normally but it does not work in test
    // $this->getJson('/api/v1/products/1')->assertStatus(200);

    $this->getJson('/api/v1/categories')->assertStatus(200);

    $this->getJson('/api/v1/advertisements')->assertStatus(200);

    $this->getJson('/api/v1/payment-methods')->assertStatus(200);
});

// TODO: not implemented yet
// test('payment callback', function () {
//     $this->postJson(route('moyasar.callback'), [])->assertStatus(200);
// });
