<?php

namespace App\Policies;

use App\Models\User;
use OwenIt\Auditing\Models\Audit;

class AuditPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('can-audit');
    }

    public function view(User $user, Audit $audit): bool
    {
        return $user->hasPermission('can-audit');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('can-audit');
    }

    public function update(User $user, Audit $audit): bool
    {
        return $user->hasPermission('can-audit');
    }

    public function delete(User $user, Audit $audit): bool
    {
        return $user->hasPermission('can-audit');
    }
}
