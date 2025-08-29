<?php

namespace App\Filament\Components;

use Filament\Forms\Components\KeyValue;

class TranslationComponent
{
    public static function configure(string $key): KeyValue
    {
        return KeyValue::make($key)
            ->addable(false)
            ->deletable(false)
            ->editableKeys(false)
            ->keyLabel('Language')
            ->valueLabel('Value')
            ->valuePlaceholder('Value')
            ->afterStateHydrated(function (KeyValue $component) {
                $component->state('{"en":"","ar":""}');
            })->required();
    }
}
