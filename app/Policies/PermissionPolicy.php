<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-permission') && $user->canManageDeveloperSettings();
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->hasPermission('view-permission') && $user->canManageDeveloperSettings();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-permission') && $user->canManageDeveloperSettings();
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->hasPermission('update-permission') && $user->canManageDeveloperSettings();
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->hasPermission('delete-permission') && $user->canManageDeveloperSettings();
    }

    public static function filterDeveloperSittings(Builder $query): Builder
    {
        return $query->where('code', '!=', 'manage-developer-settings');
    }
}
