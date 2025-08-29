<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CouponInfolist
{
    public static function configure(Schema $schema): Schema
    {
        // TODO: improve the design
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('description'),
                TextEntry::make('code'),
                TextEntry::make('type')->badge(),
                TextEntry::make('published_at')->dateTime(),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
                TextEntry::make('branch.title'),
                KeyValueEntry::make('config'),
            ]);
    }
}
