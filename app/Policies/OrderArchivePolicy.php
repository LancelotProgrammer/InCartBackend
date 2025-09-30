<?php

namespace App\Policies;

use App\Models\OrderArchive;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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
        return $user->hasPermission('create-order-archive');
    }

    public function update(User $user, OrderArchive $orderArchive): bool
    {
        return $user->hasPermission('update-order-archive');
    }

    public function delete(User $user, OrderArchive $orderArchive): bool
    {
        return $user->hasPermission('delete-order-archive');
    }
}