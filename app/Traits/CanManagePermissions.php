<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

trait CanManagePermissions
{
    protected function ensureHasRole(): void
    {
        if (! array_key_exists('role', $this->attributes)) {
            throw new InvalidArgumentException(sprintf(
                "The model %s does not have a 'role' relationship.",
                static::class
            ));
        }
    }

    public function hasPermission(string $permission): bool
    {
        return $this->role->permissions->contains('code', $permission);
    }

    public function canManageSettings(): bool
    {
        return $this->role->permissions->contains('code', 'manage-settings');
    }

    public function canViewDashboard(): bool
    {
        return $this->role->permissions->contains('code', 'view-dashboard');
    }

    public function canManageDeveloperSettings(): bool
    {
        return $this->role->permissions->contains('code', 'manage-developer-settings');
    }

    public function canPublishProduct(): bool
    {
        return $this->role->permissions->contains('code', 'publish-product');
    }

    public function canAuditOrder(): bool
    {
        return $this->role->permissions->contains('code', 'audit-order');
    }

    public function canReceiveOrderNotifications(): bool
    {
        return $this->role->permissions->contains('code', 'can-receive-order-notifications');
    }

    public function canBeAssignedToTakeOrders(): bool
    {
        return $this->role->permissions->contains('code', 'can-be-assigned-to-take-orders');
    }

    public function canBeAssignedBranch(): bool
    {
        return $this->role->permissions->contains('code', 'can-be-assigned-to-branch');
    }

    public function scopeGetUsersWhoCanReceiveOrderNotifications(Builder $query): Builder
    {
        return $query->whereHas('role.permissions', function ($q) {
            $q->where('code', 'can-receive-order-notifications');
        });
    }

    public function scopeGetUsersWhoCanBeAssignedToTakeOrders(Builder $query): Builder
    {
        return $query->whereHas('role.permissions', function ($q) {
            $q->where('code', 'can-be-assigned-to-take-orders');
        });
    }

    public function scopeGetUsersWhoCanBeAssignedToBranch(Builder $query): Builder
    {
        return $query->whereHas('role.permissions', function ($q) {
            $q->where('code', 'can-be-assigned-to-branch');
        });
    }
}
