<?php

namespace App\Traits;

use App\Models\Advertisement;
use App\Models\Coupon;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
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

    public function canViewDeliveryOrders(): bool
    {
        return $this->role->permissions->contains('code', 'view-delivery-orders-page');
    }

    public function canViewTodaysOrders(): bool
    {
        return $this->role->permissions->contains('code', 'view-todays-orders-page');
    }

    public function canViewTodayTickets(): bool
    {
        return $this->role->permissions->contains('code', 'view-todays-tickets-page');
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

    public function shouldFilterBranchContent(): bool
    {
        return $this->role->permissions->contains('code', 'filter-branch-content');
    }

    public function belongsToUserBranch(Advertisement|Order|Ticket|Feedback|Coupon $model): bool
    {
        return $this->role->permissions->contains('code', 'filter-branch-content') ?
            $this->branches->contains('id', $model->branch_id) :
            true;
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
