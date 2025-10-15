<?php

namespace App\Filament\Components;

use Closure;
use Filament\Forms\Components\KeyValue;
use Filament\Support\Enums\Operation;

class TranslationComponent
{
    public static function configure(string $key, ?bool $isRequired = true): KeyValue
    {
        $component = KeyValue::make($key)
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
            });

        if ($isRequired) {
            $component
                ->required()
                ->rules([
                    fn (): Closure => function (string $attribute, $value, Closure $fail) {
                        if (empty($value[0]['value'] ?? '') || empty($value[1]['value'] ?? '')) {
                            $fail('Both English and Arabic values are required.');
                        }
                    },
                ]);
        }

        return $component;
    }
}
