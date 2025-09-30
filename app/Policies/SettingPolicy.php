<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-setting');
    }

    public function view(User $user, Setting $setting): bool
    {
        return $user->hasPermission('view-setting');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-setting');
    }

    public function update(User $user, Setting $setting): bool
    {
        return $user->hasPermission('update-setting');
    }

    public function delete(User $user, Setting $setting): bool
    {
        return $user->hasPermission('delete-setting');
    }
}