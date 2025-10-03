<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(
                function ($record) {
                    $fileArray = [];
                    $counter = 1;
                    foreach ($record->files as $file) {
                        $fileArray[] = ImageEntry::make('product.file.id.' . $counter)->label('file ' . $counter)->state($file->url);
                        $counter++;
                    }
                    return [
                        Fieldset::make('Product Info')
                            ->columnSpanFull()
                            ->columns(4)
                            ->schema([
                                TextEntry::make('title'),
                                TextEntry::make('description'),
                                TextEntry::make('unit')
                                    ->badge(),
                                TextEntry::make('created_at')
                                    ->dateTime(),
                                TextEntry::make('categories.title')->label('Categories'),
                                TextEntry::make('brand'),
                                TextEntry::make('sku'),
                            ]),
                        Fieldset::make('Product Files')
                            ->columnSpanFull()
                            ->columns(4)
                            ->schema($fileArray),
                    ];
                }
            );
    }
}
