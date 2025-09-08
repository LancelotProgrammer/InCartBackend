<?php

if (! function_exists('get_translatable_attribute')) {
    function get_translatable_attribute(string $attribute): mixed
    {
        $translations = json_decode($attribute, true);
        if (! is_array($translations)) {
            return null;
        }
        $locale = app()->getLocale();

        return $translations[$locale] ?? $translations['en'] ?? reset($translations);
    }
}
