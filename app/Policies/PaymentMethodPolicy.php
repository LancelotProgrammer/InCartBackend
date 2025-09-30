<?php

namespace App\Policies;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentMethodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-any-payment-method') && $user->canManageDeveloperSettings();
    }

    public function view(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->hasPermission('view-payment-method') && $user->canManageDeveloperSettings();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-payment-method') && $user->canManageDeveloperSettings();
    }

    public function update(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->hasPermission('update-payment-method') && $user->canManageDeveloperSettings();
    }

    public function delete(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->hasPermission('delete-payment-method') && $user->canManageDeveloperSettings();
    }

    public function publish(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->hasPermission('publish-payment-method');
    }

    public function unpublish(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->hasPermission('unpublish-payment-method');
    }
}
