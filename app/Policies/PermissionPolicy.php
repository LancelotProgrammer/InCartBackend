<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
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

    public static function filterPermissions(Builder $query): Builder
    {
        return $query->whereNotIn(
            'code',
            [
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
        );
    }
}
