<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Support;
use App\Services\SettingsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Mews\Purifier\Facades\Purifier;

class LegalController extends Controller
{
    public function getPolicyPage(Request $request)
    {
        return view('pages.privacy-policy', [
            'privacyPolicy' => Purifier::clean(SettingsService::getPrivacyPolicy()),
        ]);
    }

    public function getTermsOfServicePage(Request $request)
    {
        return view('pages.terms-of-service', [
            'termsOfServices' => Purifier::clean(SettingsService::getTermsOfServices()),
        ]);
    }

    public function getSupportPage(Request $request)
    {
        return view('pages.support', [
            'supportEmail' => 'support@incart.com.sa', //TODO
            'supportPhone' => '+966512345678' //TODO
        ]);
    }

    public function createSupport(Request $request)
    {
        $ip = $request->ip();
        $key = 'support:' . $ip;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->with('error', 'حدث خطأ أثناء إرسال الرسالة. حاول مرة أخرى لاحقاً.');
        }

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email:rfc,dns|max:255',
            'message' => 'required|string|min:10|max:2000',
            'website' => 'nullable|string|max:0',
        ]);

        if (!empty($validated['website'])) {
            RateLimiter::hit($key, 3600);
            return back()->with('error', 'حدث خطأ أثناء إرسال الرسالة. حاول مرة أخرى لاحقاً.');
        }
        if (!$this->verifyEmailDomain($validated['email'])) {
            return back()->with('error', 'حدث خطأ أثناء إرسال الرسالة. حاول مرة أخرى لاحقاً.');
        }
        //TODO spam proof using https://akismet.com and https://github.com/josiasmontag/laravel-recaptchav3

        try {
            Support::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'message' => $validated['message'],
            ]);

            RateLimiter::hit($key, 3600);
            return back()->with('success', 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.');
        } catch (Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء إرسال الرسالة. حاول مرة أخرى لاحقاً.');
        }
    }

    private function verifyEmailDomain(string $email): bool
    {
        $domain = substr(strrchr($email, "@"), 1);
        return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
    }
}
