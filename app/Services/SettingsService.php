<?php

namespace App\Services;

use App\Constants\CacheKeys;
use App\Exceptions\SetupException;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public static function getSettings(): Collection
    {
        return Cache::remember(CacheKeys::SETTINGS, 86400, function () {
            return Setting::all();
        });
    }

    private static function getValue(string $key, bool $throwIfMissing = true): ?string
    {
        $settings = self::getSettings();
        $setting = $settings->firstWhere('key', $key);

        if (! $setting || $setting->value === null) {
            if ($throwIfMissing) {
                throw new SetupException(
                    'Something went wrong',
                    "System setup error. Please configure the `{$key}` setting."
                );
            }
            return null;
        }

        return $setting->value;
    }

    public static function isSystemOnline(): bool
    {
        return (bool) self::getValue('is_system_online');
    }

    public static function getDashboardManagers(): array
    {
        return json_decode(self::getValue('dashboard_managers'), true) ?? [];
    }

    public static function getNotificationManagers(): array
    {
        return json_decode(self::getValue('notification_managers'), true) ?? [];
    }

    public static function getWhatsapp(): ?string
    {
        return self::getValue('whatsapp', false);
    }

    public static function getTelegram(): ?string
    {
        return self::getValue('telegram', false);
    }

    public static function getFacebook(): ?string
    {
        return self::getValue('facebook', false);
    }

    public static function getServiceFee(): float
    {
        return (float) self::getValue('service_fee');
    }

    public static function getTaxRate(): float
    {
        return (float) self::getValue('tax_rate');
    }

    public static function getMinDistance(): float
    {
        return (float) self::getValue('min_distance');
    }

    public static function getMaxDistance(): float
    {
        return (float) self::getValue('max_distance');
    }

    public static function getPricePerKilometer(): float
    {
        return (float) self::getValue('price_per_kilometer');
    }

    public static function getPrivacyPolicy(): ?string
    {
        return self::getValue('privacy_policy', false);
    }

    public static function getTermsOfServices(): ?string
    {
        return self::getValue('terms_of_services', false);
    }

    public static function getAllowedTicketCount(): ?int
    {
        return self::getValue('allowed_ticket_count');
    }

    public static function getAllowedFeedbackCount(): ?int
    {
        return self::getValue('allowed_feedback_count');
    }
}
