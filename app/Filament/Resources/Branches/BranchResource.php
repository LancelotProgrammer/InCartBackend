<?php

namespace App\Filament\Resources\Branches;

use App\Filament\Resources\Branches\Pages\EditBranch;
use App\Filament\Resources\Branches\Pages\ListBranches;
use App\Filament\Resources\Branches\RelationManagers\AdvertisementsRelationManager;
use App\Filament\Resources\Branches\RelationManagers\CouponsRelationManager;
use App\Filament\Resources\Branches\RelationManagers\PaymentMethodsRelationManager;
use App\Filament\Resources\Branches\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\Branches\RelationManagers\UsersRelationManager;
use App\Filament\Resources\Branches\Schemas\BranchForm;
use App\Filament\Resources\Branches\Schemas\BranchInfolist;
use App\Filament\Resources\Branches\Tables\BranchesTable;
use App\Models\Branch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static string|UnitEnum|null $navigationGroup = 'Configs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return BranchForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BranchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BranchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AdvertisementsRelationManager::class,
            CouponsRelationManager::class,
            ProductsRelationManager::class,
            PaymentMethodsRelationManager::class,
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBranches::route('/'),
            'edit' => EditBranch::route('/{record}/edit'),
        ];
    }
}
