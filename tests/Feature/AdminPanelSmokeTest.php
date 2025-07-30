<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = User::firstOrCreate(
        ['email' => 'admin@admin.com'],
        [
            'name' => 'Admin',
            'password' => Hash::make('admin123'),
        ]
    );
});

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
