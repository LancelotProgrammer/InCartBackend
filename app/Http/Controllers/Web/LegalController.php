<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Mews\Purifier\Facades\Purifier;

class LegalController extends Controller
{
    public function getPolicyPage(Request $request)
    {
        return view('pages.privacy-policy', [
            'privacyPolicy' => Purifier::clean(SettingsService::getPrivacyPolicy())
        ]);
    }

    public function getTermsOfServicePage(Request $request)
    {
        return view('pages.terms-of-service', [
            'termsOfServices' => Purifier::clean(SettingsService::getTermsOfServices())
        ]);
    }
}
