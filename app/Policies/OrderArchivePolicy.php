<?php

namespace App\Policies;

use App\Models\OrderArchive;
use App\Models\User;

class OrderArchivePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-order-archive');
    }

    public function view(User $user, OrderArchive $orderArchive): bool
    {
        return $user->hasPermission('view-order-archive');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, OrderArchive $orderArchive): bool
    {
        return false;
    }

    public function delete(User $user, OrderArchive $orderArchive): bool
    {
        return $user->hasPermission('delete-order-archive');
    }
}
