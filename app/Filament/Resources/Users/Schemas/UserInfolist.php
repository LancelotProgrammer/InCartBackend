<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(trans('dashboard.users.name')),
                TextEntry::make('email')
                    ->label(trans('dashboard.users.email')),
                TextEntry::make('phone')
                    ->label(trans('dashboard.users.phone')),
            ]);
    }
}
