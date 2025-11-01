<?php

namespace App\Services;

class BranchSettingsService
{
    public static function getServiceFee(int $branchId): float
    {
        return self::getSetting($branchId, 'service_fee', SettingsService::getServiceFee());
    }

    public static function getTaxRate(int $branchId): float
    {
        return self::getSetting($branchId, 'tax_rate', SettingsService::getTaxRate());
    }

    public static function getMinDistance(int $branchId): float
    {
        return self::getSetting($branchId, 'min_distance', SettingsService::getMinDistance());
    }

    public static function getMaxDistance(int $branchId): float
    {
        return self::getSetting($branchId, 'max_distance', SettingsService::getMaxDistance());
    }

    public static function getPricePerKilometer(int $branchId): float
    {
        return self::getSetting($branchId, 'price_per_kilometer', SettingsService::getPricePerKilometer());
    }

    public static function getMaxSubtotalPrice(int $branchId): float
    {
        return self::getSetting($branchId, 'max_subtotal_price', SettingsService::getMaxSubtotalPrice());
    }

    public static function getMaxScheduledDays(int $branchId): float
    {
        return self::getSetting($branchId, 'max_scheduled_days', SettingsService::getMaxScheduledDays());
    }

    protected static function getSetting(int $branchId, string $key, float $default): mixed
    {
        // future:
        // $value = BranchSetting::query()
        //     ->where('branch_id', $branchId)
        //     ->where('key_name', $key)
        //     ->value('value');

        // return $value !== null ? (float) $value : $default;

        return $default;
    }
}
