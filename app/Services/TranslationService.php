<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class TranslationService
{
    public static function getTranslatableAttribute(string $attribute): mixed
    {
        $translations = json_decode($attribute, true);
        if (! is_array($translations)) {
            Log::channel('app_log')->warning('Helpers(get_translatable_attribute): translations attribute is not array', ['attribute' => $attribute]);
            return null;
        }
        $locale = app()->getLocale();

        return $translations[$locale] ?? $translations['en'] ?? reset($translations);
    }
}
