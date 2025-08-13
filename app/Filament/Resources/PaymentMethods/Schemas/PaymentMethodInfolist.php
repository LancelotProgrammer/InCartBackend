<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PaymentMethodInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order')
                    ->numeric(),
                TextEntry::make('published_at')
                    ->dateTime(),
                TextEntry::make('branch.title')
                    ->numeric(),
            ]);
    }
}
