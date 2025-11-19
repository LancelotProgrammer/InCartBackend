<?php

namespace App\Pipes;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;

class GetSettings
{
    public function __invoke(Request $request, Closure $next): array
    {
        return $next([
            'whatsapp' => SettingsService::getWhatsapp(),
            'tiktok' => SettingsService::getTiktok(),
            'facebook' => SettingsService::getFacebook(),
            'twitter' => SettingsService::getTwitter(),
            'instagram' => SettingsService::getInstagram(),
            'phone' => SettingsService::getPhone(),
            'privacy_policy' => SettingsService::getPrivacyPolicy(),
            'terms_of_services' => SettingsService::getTermsOfServices(),
            'faqs' => SettingsService::getFaqs(),
            'loyalty_policy' => SettingsService::getLoyaltyPolicy(),
            'about_us' => SettingsService::getAboutUs(),
            'return_policy' => SettingsService::getReturnPolicy(),
            'max_scheduled_days' => SettingsService::getMaxScheduledDays(),
        ]);
    }
}
