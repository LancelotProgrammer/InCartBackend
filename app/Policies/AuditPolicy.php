<?php

namespace App\Policies;

use App\Models\User;
use OwenIt\Auditing\Models\Audit;

class AuditPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('can-view-audit') && $user->canManageDeveloperSettings();
    }

    public function view(User $user, Audit $audit): bool
    {
        return $user->hasPermission('can-view-audit') && $user->canManageDeveloperSettings();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Audit $audit): bool
    {
        return false;
    }

    public function delete(User $user, Audit $audit): bool
    {
        return false;
    }
}
