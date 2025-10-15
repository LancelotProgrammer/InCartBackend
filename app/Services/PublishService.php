<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\PaymentMethod;

class PublishService
{
    public static function canPublish(string $model, $record): array
    {
        return match ($model) {
            Branch::class => PublishService::validatePublishBranch($record),
            default => [true, ''],
        };
    }

    public static function canUnpublish(string $model, $record): array
    {
        return match ($model) {
            PaymentMethod::class => PublishService::validateUnpublishPayOnDeliveryPaymentMethod($record),
            default => [true, ''],
        };
    }

    private static function validatePublishBranch(Branch $record): array
    {
        $condition1 = $record->deliveryUsers()->exists();
        $condition2 = $record->notificationUsers()->exists();
        $condition3 = PaymentMethod::where('code', '=', PaymentMethod::PAY_ON_DELIVERY_CODE)
            ->where('branch_id', '=', $record->id)
            ->whereNotNull('published_at')
            ->exists();

        $reasons = [];

        if (! $record->deliveryUsers()->exists()) {
            $reasons[] = 'No delivery users are assigned to this branch.';
        }

        if (! $record->notificationUsers()->exists()) {
            $reasons[] = 'No users are assigned to receive order notifications.';
        }

        $hasPayOnDelivery = PaymentMethod::where('code', PaymentMethod::PAY_ON_DELIVERY_CODE)
            ->where('branch_id', $record->id)
            ->whereNotNull('published_at')
            ->exists();

        if (! $hasPayOnDelivery) {
            $reasons[] = 'The "Pay on Delivery" payment method must be active for this branch.';
        }

        $reason = $reasons
            ? 'This branch cannot be published because: '.implode(' ', $reasons)
            : 'This branch cannot be published due to unknown validation failure.';

        return [
            $condition1 && $condition2 && $condition3,
            $reason,
        ];
    }

    private static function validateUnpublishPayOnDeliveryPaymentMethod(PaymentMethod $record): array
    {
        $condition = ! ($record->code === PaymentMethod::PAY_ON_DELIVERY_CODE);
        $reason = 'This payment method cannot be unpublished because it is required by other components.';

        return [
            $condition,
            $reason,
        ];
    }
}
