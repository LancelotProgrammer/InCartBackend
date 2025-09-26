<?php

use App\Models\User;

beforeEach(function () {
    $this->admin = User::where('email', 'owner@owner.com')->first();
});

describe('API smoke test', function () {
    test('the application returns a successful response', function () {
        $this->get('/')->assertStatus(200);
    });

    test('the login page returns a successful response', function () {
        $this->get('/admin/login')->assertStatus(200);
    });

    test('the admin page returns a successful response', function () {
        $this->actingAs($this->admin)->get('/admin')->assertStatus(200);
    });

    test('the docs page returns a successful response', function () {
        $this->actingAs($this->admin)->get('/admin/users')->assertStatus(200);
    });
})->group('dashboard-smoke-test');
