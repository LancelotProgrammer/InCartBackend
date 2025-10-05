<?php

namespace App\Services;

use App\Constants\CacheKeys;
use App\Exceptions\SetupException;
use App\Models\Setting;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
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

    public static function getSettingsComponents(): array
    {
        return [
            Section::make('Service')
                ->description('Is system online or offline')
                ->components([
                    Checkbox::make('is_system_online')->inline(false),
                ]),
            Section::make('Support')
                ->description('Support and feedback allowed for user per day')
                ->columns(2)
                ->components([
                    TextInput::make('allowed_ticket_count')->integer(),
                    TextInput::make('allowed_feedback_count')->integer(),
                ]),
            Section::make('Social Media Links')
                ->columnSpanFull()
                ->description('User who will receive system notifications')
                ->columns(5)
                ->components([
                    TextInput::make('whatsapp')->url(),
                    TextInput::make('tiktok')->url(),
                    TextInput::make('facebook')->url(),
                    TextInput::make('twitter')->url(),
                    TextInput::make('instagram')->url(),
                ]),
            Section::make('Order Config')
                ->columnSpanFull()
                ->description('Is is system online')
                ->columns(5)
                ->components([
                    TextInput::make('service_fee')->numeric(),
                    TextInput::make('tax_rate')->numeric(),
                    TextInput::make('min_distance')->numeric(),
                    TextInput::make('max_distance')->numeric(),
                    TextInput::make('price_per_kilometer')->numeric(),
                ]),
            Section::make('Legal')
                ->description('Legal text')
                ->columnSpanFull()
                ->components([
                    RichEditor::make('privacy_policy'),
                    RichEditor::make('terms_of_services'),
                    RichEditor::make('faqs'),
                    RichEditor::make('loyalty_policy'),
                    RichEditor::make('about_us'),
                    RichEditor::make('return_policy'),
                ]),
        ];
    }

    public static function isSystemOnline(): bool
    {
        return (bool) self::getValue('is_system_online');
    }

    public static function getWhatsapp(): ?string
    {
        return self::getValue('whatsapp', false);
    }

    public static function getTiktok(): ?string
    {
        return self::getValue('tiktok', false);
    }

    public static function getFacebook(): ?string
    {
        return self::getValue('facebook', false);
    }

    public static function getTwitter(): ?string
    {
        return self::getValue('twitter', false);
    }

    public static function getInstagram(): ?string
    {
        return self::getValue('instagram', false);
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

    public static function getFaqs(): ?string
    {
        return self::getValue('faqs', false);
    }

    public static function getLoyaltyPolicy(): ?string
    {
        return self::getValue('loyalty_policy', false);
    }

    public static function getAboutUs(): ?string
    {
        return self::getValue('about_us', false);
    }

    public static function getReturnPolicy(): ?string
    {
        return self::getValue('return_policy', false);
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
