<?php

namespace App\Filament\Components;

use Filament\Forms\Components\KeyValue;
use Filament\Support\Enums\Operation;

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
            ->afterStateHydrated(function (KeyValue $component, string $operation) {
                if ($operation === Operation::Create->value) {
                    $component->state('{"en":"","ar":""}');
                }
            })->required();
    }
}
