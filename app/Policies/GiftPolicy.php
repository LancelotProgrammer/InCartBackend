<?php

namespace App\Policies;

use App\Models\Gift;
use App\Models\User;

class GiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-gift') && $user->canManageDeveloperSettings();
    }

    public function view(User $user, Gift $gift): bool
    {
        return $user->hasPermission('view-gift') && $user->canManageDeveloperSettings();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-gift') && $user->canManageDeveloperSettings();
    }

    public function update(User $user, Gift $gift): bool
    {
        return $user->hasPermission('update-gift') && $user->canManageDeveloperSettings();
    }

    public function delete(User $user, Gift $gift): bool
    {
        return $user->hasPermission('delete-gift') && $user->canManageDeveloperSettings();
    }

    public function publish(User $user, Gift $gift): bool
    {
        return $user->hasPermission('publish-gift') && $user->canManageDeveloperSettings();
    }

    public function unpublish(User $user, Gift $gift): bool
    {
        return $user->hasPermission('unpublish-gift') && $user->canManageDeveloperSettings();
    }

    public function showCode(User $user, Gift $gift): bool
    {
        return $user->hasPermission('show-code-gift') && $user->canManageDeveloperSettings();
    }
}
