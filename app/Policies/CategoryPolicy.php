<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-category');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->hasPermission('view-category');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-category');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->hasPermission('update-category');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermission('delete-category');
    }

    public function publish(User $user, Category $category): bool
    {
        return $user->hasPermission('publish-category');
    }

    public function unpublish(User $user, Category $category): bool
    {
        return $user->hasPermission('unpublish-category');
    }

    public function viewProducts(User $user, Category $category): bool
    {
        return $user->hasPermission('view-products-category');
    }

    public function viewCategories(User $user, Category $category): bool
    {
        return $user->hasPermission('view-categories-category');
    }
}
