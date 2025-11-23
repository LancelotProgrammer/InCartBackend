<?php

namespace App\Policies;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-city');
    }

    public function view(User $user, City $city): bool
    {
        return $user->hasPermission('view-city');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-city');
    }

    public function update(User $user, City $city): bool
    {
        return $user->hasPermission('update-city');
    }

    public function delete(User $user, City $city): bool
    {
        return $user->hasPermission('delete-city');
    }

    public function publish(User $user, City $city): bool
    {
        return $user->hasPermission('publish-city');
    }

    public function unpublish(User $user, City $city): bool
    {
        return $user->hasPermission('unpublish-city');
    }

    public static function filterCities(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin() || $user->isDeveloper()) {
            return $query;
        }

        return $query->where('id', '=', $user->city_id);
    }
}
