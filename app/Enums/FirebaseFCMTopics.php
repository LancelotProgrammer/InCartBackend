<?php

namespace App\Enums;

use App\Models\City;
use App\Policies\CityPolicy;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Facades\Log;

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
        Log::channel('app_log')->info('Enums: getting topics');

        $user = auth()->user();
        $topics = [];

        if ($user->isSuperAdmin() || $user->isDeveloper()) {
            $topics = [
                self::ALL_USERS->value => 'All Users',
            ];
        }

        foreach (CityPolicy::filterCities(City::query()->published(), $user)->get() as $city) {
            $topics[self::CITY->value.'-'.$city->id] = 'All Users in '.$city->name;
        }

        Log::channel('app_log')->info('Enums: topics', [
            'topics' => $topics,
            'user' => $user->name
        ]);

        return $topics;
    }
}
