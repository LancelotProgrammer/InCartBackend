<?php

namespace App\Enums;

use App\Models\City;
use Filament\Support\Contracts\HasLabel;

enum FirebaseFCMTopics: string implements HasLabel
{
    case ALL_USERS = 'all-users';
    case CITY = 'city';

    public function getLabel(): string
    {
        return match ($this) {
            self::ALL_USERS => 'All Users',
            self::CITY => 'City',
        };
    }

    public static function getTopics(): array
    {
        $topics = [
            self::ALL_USERS->value => 'All Users',
        ];

        foreach (City::all() as $city) {
            $topics[self::CITY->value.'-'.$city->id] = 'All Users in '.$city->name;
        }

        return $topics;
    }
}
