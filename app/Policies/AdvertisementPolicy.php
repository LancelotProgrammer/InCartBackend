<?php

namespace App\Policies;

use App\Models\Advertisement;
use App\Models\User;

class AdvertisementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-advertisement');
    }

    public function view(User $user, Advertisement $advertisement): bool
    {
        return $user->hasPermission('view-advertisement');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-advertisement');
    }

    public function update(User $user, Advertisement $advertisement): bool
    {
        return $user->hasPermission('update-advertisement');
    }

    public function delete(User $user, Advertisement $advertisement): bool
    {
        return $user->hasPermission('delete-advertisement');
    }

    public function publish(User $user, Advertisement $advertisement): bool
    {
        return $user->hasPermission('publish-advertisement');
    }

    public function unpublish(User $user, Advertisement $advertisement): bool
    {
        return $user->hasPermission('unpublish-advertisement');
    }
}
