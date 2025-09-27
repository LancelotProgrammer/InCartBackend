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
            'telegram' => SettingsService::getTelegram(),
            'facebook' => SettingsService::getFacebook(),
            'privacy_policy' => SettingsService::getPrivacyPolicy(),
            'terms_of_services' => SettingsService::getTermsOfServices(),
            'faqs' => SettingsService::getFaqs(),
        ]);
    }
}
