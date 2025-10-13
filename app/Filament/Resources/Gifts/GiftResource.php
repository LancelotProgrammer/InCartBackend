<?php

namespace App\Filament\Resources\Gifts;

use App\Filament\Resources\Gifts\Pages\CreateGift;
use App\Filament\Resources\Gifts\Pages\ListGifts;
use App\Filament\Resources\Gifts\Schemas\GiftForm;
use App\Filament\Resources\Gifts\Schemas\GiftInfolist;
use App\Filament\Resources\Gifts\Tables\GiftsTable;
use App\Models\Gift;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GiftResource extends Resource
{
    protected static ?string $model = Gift::class;

    protected static string|UnitEnum|null $navigationGroup = 'Configs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Gift;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return GiftForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GiftInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GiftsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGifts::route('/'),
            'create' => CreateGift::route('/create'),
        ];
    }
}
