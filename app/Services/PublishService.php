<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\City;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Log;

class PublishService
{
    public static function canPublish(string $model, $record): array
    {
        return match ($model) {
            Branch::class => PublishService::validatePublishBranch($record),
            City::class => PublishService::validatePublishCity($record),
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
        Log::channel('app_log')->info('PublishService: validatePublishBranch');

        $condition1 = $record->deliveryUsers()->exists();
        $condition2 = $record->notificationUsers()->exists();
        $condition3 = PaymentMethod::where('code', '=', PaymentMethod::PAY_ON_DELIVERY_CODE)
            ->where('branch_id', '=', $record->id)
            ->whereNotNull('published_at')
            ->exists();

        $reasons = [];

        if (! $condition1) {
            Log::channel('app_log')->warning("Services(PublishService): No delivery users for branch {$record->id}");
            $reasons[] = 'No delivery users are assigned to this branch.';
        }

        if (! $condition2) {
            Log::channel('app_log')->warning("Services(PublishService): No notification users for branch {$record->id}");
            $reasons[] = 'No users are assigned to receive order notifications.';
        }

        if (! $condition3) {
            Log::channel('app_log')->warning("Services(PublishService): No pay on delivery payment method for branch {$record->id}");
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

    private static function validatePublishCity(City $record): array
    {
        $condition = $record->branches()->published()->exists();
        $reason = 'This city cannot be published because it has no published branches.';
        
        if (! $condition) {
            Log::channel('app_log')->warning("Services(PublishService): No published branches for city {$record->id}");
        }

        return [
            $condition,
            $reason,
        ];
    }

    private static function validateUnpublishPayOnDeliveryPaymentMethod(PaymentMethod $record): array
    {
        $condition = ! ($record->code === PaymentMethod::PAY_ON_DELIVERY_CODE);
        $reason = 'This payment method cannot be unpublished because it is required by other components.';

        if (! $condition) {
            Log::channel('app_log')->warning("Services(PublishService): Payment method {$record->id} is required by other components");
        }

        return [
            $condition,
            $reason,
        ];
    }
}
