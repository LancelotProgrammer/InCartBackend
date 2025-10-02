<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-user');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermission('view-user');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-user');
    }

    public function update(User $user, User $model): bool
    {
        if ($this->isSuperAdminOrDeveloper($model)) {
            return false;
        }

        return $user->hasPermission('update-user');
    }

    public function delete(User $user, User $model): bool
    {
        if ($this->isSuperAdminOrDeveloper($model)) {
            return false;
        }

        return $user->hasPermission('delete-user');
    }

    public function block(User $user, User $model): bool
    {
        if ($this->isSuperAdminOrDeveloper($model)) {
            return false;
        }

        return $user->hasPermission('block-user');
    }

    public function unblock(User $user, User $model): bool
    {
        if ($this->isSuperAdminOrDeveloper($model)) {
            return false;
        }

        return $user->hasPermission('unblock-user');
    }

    private function isSuperAdminOrDeveloper(User $model): bool
    {
        return $model->role->code === Role::ROLE_SUPER_ADMIN_CODE || $model->role->code === Role::ROLE_DEVELOPER_CODE;
    }
}
