<?php

namespace App\Policies;

use App\Models\Support;
use App\Models\User;

class SupportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-support');
    }

    public function view(User $user, Support $support): bool
    {
        return $user->hasPermission('view-support');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Support $support): bool
    {
        return false;
    }

    public function delete(User $user, Support $support): bool
    {
        return $user->hasPermission('delete-support');
    }

    public function process(User $user, Support $support): bool
    {
        return $user->hasPermission('process-support');
    }
}
