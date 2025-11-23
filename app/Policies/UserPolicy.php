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
        if ($this->isCustomer($model)) {
            return false;
        }

        if ($this->isSuperAdminOrDeveloper($model)) {
            return false;
        }

        if ($this->canNotInteractWithUser($user, $model)) {
            return false;
        }

        return $user->hasPermission('update-user');
    }

    public function delete(User $user, User $model): bool
    {
        if ($this->isSuperAdminOrDeveloper($model)) {
            return false;
        }

        if ($this->canNotInteractWithUser($user, $model)) {
            return false;
        }

        return $user->hasPermission('delete-user');
    }

    public function block(User $user, User $model): bool
    {
        if ($this->isSuperAdminOrDeveloper($model)) {
            return false;
        }

        if ($this->canNotInteractWithUser($user, $model)) {
            return false;
        }

        return $user->hasPermission('block-user');
    }

    public function unblock(User $user, User $model): bool
    {
        if ($this->isSuperAdminOrDeveloper($model)) {
            return false;
        }

        if ($this->canNotInteractWithUser($user, $model)) {
            return false;
        }

        return $user->hasPermission('unblock-user');
    }

    public function approve(User $user, User $model): bool
    {
        if ($this->isSuperAdminOrDeveloper($model)) {
            return false;
        }

        if (! $this->isCustomer($model)) {
            return false;
        }

        if ($this->canNotInteractWithUser($user, $model)) {
            return false;
        }

        return $user->hasPermission('approve-user');
    }

    public function disapprove(User $user, User $model): bool
    {
        if ($this->isSuperAdminOrDeveloper($model)) {
            return false;
        }

        if (! $this->isCustomer($model)) {
            return false;
        }

        if ($this->canNotInteractWithUser($user, $model)) {
            return false;
        }

        return $user->hasPermission('disapprove-user');
    }

    public function sendNotification(User $user): bool
    {
        return $user->hasPermission('send-notification');
    }

    private function canNotInteractWithUser(User $user, User $model): bool
    {
        if ($this->isSuperAdminOrDeveloper($user)) {
            return false;
        }

        return $user->city_id !== $model->city_id;
    }

    private function isSuperAdminOrDeveloper(User $model): bool
    {
        return $model->role->code === Role::ROLE_SUPER_ADMIN_CODE || $model->role->code === Role::ROLE_DEVELOPER_CODE;
    }

    private function isCustomer(User $model): bool
    {
        return $model->role->code === Role::ROLE_CUSTOMER_CODE;
    }
}
