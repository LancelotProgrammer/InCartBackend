<?php

namespace Database\Seeders;

use App\Enums\SettingType;
use App\Models\Setting;

class SettingsData
{
    public static function run(): void
    {
        Setting::insert([
            // Service
            [
                'key' => 'is_system_online',
                'value' => '1',
                'type' => SettingType::BOOL,
                'group' => 'service',
                'is_locked' => false,
            ],

            // Social Media
            [
                'key' => 'whatsapp',
                'value' => 'http://localhost:8000',
                'type' => SettingType::STR,
                'group' => 'social',
                'is_locked' => false,
            ],
            [
                'key' => 'tiktok',
                'value' => 'http://localhost:8000',
                'type' => SettingType::STR,
                'group' => 'social',
                'is_locked' => false,
            ],
            [
                'key' => 'facebook',
                'value' => 'http://localhost:8000',
                'type' => SettingType::STR,
                'group' => 'social',
                'is_locked' => false,
            ],
            [
                'key' => 'twitter',
                'value' => 'http://localhost:8000',
                'type' => SettingType::STR,
                'group' => 'social',
                'is_locked' => false,
            ],
            [
                'key' => 'instagram',
                'value' => 'http://localhost:8000',
                'type' => SettingType::STR,
                'group' => 'social',
                'is_locked' => false,
            ],
            [
                'key' => 'phone',
                'value' => '+966512345678',
                'type' => SettingType::STR,
                'group' => 'social',
                'is_locked' => false,
            ],

            // Order Config
            [
                'key' => 'service_fee',
                'value' => '1',
                'type' => SettingType::FLOAT,
                'group' => 'order',
                'is_locked' => false,
            ],
            [
                'key' => 'tax_rate',
                'value' => '0',
                'type' => SettingType::FLOAT,
                'group' => 'order',
                'is_locked' => false,
            ],
            [
                'key' => 'min_distance',
                'value' => '0',
                'type' => SettingType::FLOAT,
                'group' => 'order',
                'is_locked' => false,
            ],
            [
                'key' => 'max_distance',
                'value' => '100',
                'type' => SettingType::FLOAT,
                'group' => 'order',
                'is_locked' => false,
            ],
            [
                'key' => 'price_per_kilometer',
                'value' => '2',
                'type' => SettingType::FLOAT,
                'group' => 'order',
                'is_locked' => false,
            ],
            [
                'key' => 'max_subtotal_price',
                'value' => '10000',
                'type' => SettingType::FLOAT,
                'group' => 'order',
                'is_locked' => false,
            ],
            [
                'key' => 'max_scheduled_days',
                'value' => '7',
                'type' => SettingType::INT,
                'group' => 'order',
                'is_locked' => false,
            ],

            // Legal
            [
                'key' => 'privacy_policy',
                'value' => 'privacy_policy',
                'type' => SettingType::STR,
                'group' => 'legal',
                'is_locked' => false,
            ],
            [
                'key' => 'terms_of_services',
                'value' => 'terms_of_services',
                'type' => SettingType::STR,
                'group' => 'legal',
                'is_locked' => false,
            ],
            [
                'key' => 'faqs',
                'value' => 'faqs',
                'type' => SettingType::STR,
                'group' => 'legal',
                'is_locked' => false,
            ],
            [
                'key' => 'loyalty_policy',
                'value' => 'loyalty_policy',
                'type' => SettingType::STR,
                'group' => 'legal',
                'is_locked' => false,
            ],
            [
                'key' => 'about_us',
                'value' => 'about_us',
                'type' => SettingType::STR,
                'group' => 'legal',
                'is_locked' => false,
            ],
            [
                'key' => 'return_policy',
                'value' => 'return_policy',
                'type' => SettingType::STR,
                'group' => 'legal',
                'is_locked' => false,
            ],

            // support
            [
                'key' => 'allowed_ticket_count',
                'value' => '5',
                'type' => SettingType::INT,
                'group' => 'support',
                'is_locked' => false,
            ],
            [
                'key' => 'allowed_feedback_count',
                'value' => '5',
                'type' => SettingType::INT,
                'group' => 'support',
                'is_locked' => false,
            ],
        ]);
    }
}
