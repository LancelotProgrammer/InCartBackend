<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Branch;
use App\Models\City;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class PublishService
{
    public static function canPublish(string $model, $record): array
    {
        return match ($model) {
            Branch::class => PublishService::validatePublishBranch($record),
            City::class => PublishService::validatePublishCity($record),
            default => [true, 'Record is published successfully.'],
        };
    }

    public static function canUnpublish(string $model, $record): array
    {
        return match ($model) {
            PaymentMethod::class => PublishService::validateUnpublishPayOnDeliveryPaymentMethod($record),
            Branch::class => PublishService::validateUnpublishBranch($record),
            default => [true, 'Record is unpublished successfully.'],
        };
    }

    private static function validatePublishBranch(Branch $record): array
    {
        Log::channel('app_log')->info('Services(PublishService): validatePublishBranch', ['branch_id' => $record->id]);

        // Critical conditions
        $condition1 = $record->deliveryUsers()->exists();
        $condition2 = $record->notificationUsers()->exists();
        $condition3 = PaymentMethod::where('code', PaymentMethod::PAY_ON_DELIVERY_CODE)
            ->where('branch_id', $record->id)
            ->whereNotNull('published_at')
            ->exists();
        $condition4 = $record->products()->count() === 30;
        $condition5 = $record->products()
            ->wherePivotNotNull('published_at')
            ->count() === Product::count();
        $condition6 = $record->advertisements()->count() !== 0;

        $criticalFails = [];
        $warningFails = [];

        if (! $condition1) {
            $criticalFails[] = 'There are no delivery users for this branch.';
            Log::warning('Services(PublishService): No delivery users for branch');
        }
        if (! $condition2) {
            $criticalFails[] = 'No users are assigned to receive order notifications.';
            Log::warning('Services(PublishService): No notification users for branch');
        }
        if (! $condition3) {
            $criticalFails[] = 'The (Pay on Delivery) method must be active for this branch.';
            Log::warning('Services(PublishService): No pay on delivery method for branch');
        }
        if (! $condition4) {
            $criticalFails[] = 'Some products are not configured for this branch.';
            Log::warning('Services(PublishService): Products not fully configured for branch');
        }

        if ($criticalFails) {
            return [
                false,
                'This branch cannot be published because: ' . implode(' ', $criticalFails),
            ];
        }

        if (! $condition5) {
            $warningFails[] = 'Some products are not published for this branch.';
            Log::notice('Services(PublishService): Some products not published for branch');
        }
        if (! $condition6) {
            $warningFails[] = 'This branch has not advertisements.';
            Log::notice('Services(PublishService): Branch has no advertisements');
        }

        if ($warningFails) {
            return [
                true,
                'Branch can be published, but there are some warnings: ' . implode(' ', $warningFails),
            ];
        }

        return [
            true,
            'Branch is published successfully.',
        ];
    }

    private static function validatePublishCity(City $record): array
    {
        Log::channel('app_log')->info('Services(PublishService): validatePublishCity', ['city_id' => $record->id]);

        $condition = $record->branches()->published()->exists();

        $criticalFails = [];

        if (! $condition) {
            $criticalFails[] = 'This city cannot be published because it has no published branches.';
            Log::channel('app_log')->warning('Services(PublishService): This city cannot be published because it has no published branches');
        }

        if ($criticalFails) {
            return [
                false,
                'This city cannot be published because: ' . implode(' ', $criticalFails),
            ];
        }

        return [
            true,
            'City is published successfully.',
        ];
    }

    private static function validateUnpublishPayOnDeliveryPaymentMethod(PaymentMethod $record): array
    {
        Log::channel('app_log')->info('Services(PublishService): validateUnpublishPayOnDeliveryPaymentMethod', ['payment_method_id' => $record->id]);

        $condition = ! ($record->code === PaymentMethod::PAY_ON_DELIVERY_CODE);
        
        $criticalFails = [];

        if (! $condition) {
            $criticalFails[] = 'This payment method cannot be unpublished because it is required by other components.';
            Log::channel('app_log')->warning('Services(PublishService): This payment method cannot be unpublished because it is required by other components');
        }

        if ($criticalFails) {
            return [
                false,
                'This payment method cannot be unpublished because: ' . implode(' ', $criticalFails),
            ];
        }

        return [
            true,
            'Payment method is unpublished successfully.',
        ];
    }

    private static function validateUnpublishBranch(Branch $record): array
    {
        Log::channel('app_log')->info('Services(PublishService): validateUnpublishBranch', ['branch_id' => $record->id]);

        $condition1 = Branch::whereNotNull('published_at')->count() !== 1 && $record->published_at !== null;
        $condition2 = $record->orders()
            ->whereIn('order_status', [OrderStatus::PROCESSING->value, OrderStatus::DELIVERING->value, OrderStatus::FINISHED->value])
            ->exists();

        $criticalFails = [];

        if (! $condition1) {
            $criticalFails[] = 'This is the only branch and cannot be unpublished';
            Log::channel('app_log')->warning('Services(PublishService): This is the only branch and cannot be unpublished');
        }
        if (! $condition2) {
            $criticalFails[] = 'This branch has uncompleted orders';
            Log::channel('app_log')->warning('Services(PublishService): This branch has uncompleted orders');
        }

        if ($criticalFails) {
            return [
                false,
                'This branch cannot be unpublished because: ' . implode(' ', $criticalFails),
            ];
        }

        return [
            true,
            'Branch is unpublished successfully.',
        ];
    }
}
