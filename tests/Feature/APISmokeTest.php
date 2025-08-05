<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('the user api returns a successful response', function () {
    Sanctum::actingAs(User::factory()->create(), ['*']);
    $this->getJson('/api/v1/auth/user')->assertStatus(200);
});
