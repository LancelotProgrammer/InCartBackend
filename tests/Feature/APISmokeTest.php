<?php

use App\Models\User;

test('the user api returns a successful response', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->getJson('/api/v1/user')->assertStatus(200);
});

test('the test api returns a successful response', function () {
    $this->getJson('/api/v1/test')->assertStatus(200);
});
