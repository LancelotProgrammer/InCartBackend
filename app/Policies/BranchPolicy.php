<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-branch') && $user->canManageDeveloperSettings();
    }

    public function view(User $user, Branch $branch): bool
    {
        return $user->hasPermission('view-branch') && $user->canManageDeveloperSettings();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-branch') && $user->canManageDeveloperSettings();
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->hasPermission('update-branch') && $user->canManageDeveloperSettings();
    }

    public function delete(User $user, Branch $branch): bool
    {
        return $user->hasPermission('delete-branch') && $user->canManageDeveloperSettings();
    }

    public function markAsDefault(User $user, Branch $branch): bool
    {
        return $user->hasPermission('mark-default-branch') && $user->canManageDeveloperSettings();
    }

    public function unmarkAsDefault(User $user, Branch $branch): bool
    {
        return $user->hasPermission('unmark-default-branch') && $user->canManageDeveloperSettings();
    }

    public function publish(User $user, Branch $branch): bool
    {
        return $user->hasPermission('publish-branch') && $user->canManageDeveloperSettings();
    }

    public function unpublish(User $user, Branch $branch): bool
    {
        return $user->hasPermission('unpublish-branch') && $user->canManageDeveloperSettings();
    }
}