<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('API smoke test', function () {
    test('the user api returns a successful response', function () {
        Sanctum::actingAs(User::factory()->create(), ['*']);
        $this->getJson('/api/v1/auth/user')->assertStatus(200);
    });
})->group('api-smoke-test');
