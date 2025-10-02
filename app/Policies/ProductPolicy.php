<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-product');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasPermission('view-product');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-product');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasPermission('update-product');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermission('delete-product');
    }
}
