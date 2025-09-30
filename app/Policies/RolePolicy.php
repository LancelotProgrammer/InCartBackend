<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-role');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission('view-role');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-role');
    }

    public function update(User $user, Role $role): bool
    {
        if ($this->isSuperAdminOrDeveloper($role)) {
            return false;
        }

        return $user->hasPermission('update-role');
    }

    public function delete(User $user, Role $role): bool
    {
        if ($this->isSuperAdminOrDeveloper($role)) {
            return false;
        }

        return $user->hasPermission('delete-role');
    }

    private function isSuperAdminOrDeveloper(Role $role): bool
    {
        return in_array($role->code, [Role::ROLE_SUPER_ADMIN_CODE, Role::ROLE_DEVELOPER_CODE]);
    }

    public static function filterOwnerAndDeveloper(Builder $query): Builder
    {
        return $query->where('code', '!=', Role::ROLE_DEVELOPER_CODE)->where('code', '!=', Role::ROLE_SUPER_ADMIN_CODE);
    }
}
