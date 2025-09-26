<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use App\Enums\AdvertisementLink;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class AdvertisementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(4)
            ->components(function ($record) {
                $typeArray = [];
                if ($record->link === AdvertisementLink::PRODUCT) {
                    $typeArray[] = TextEntry::make('product.title')->label('product');
                    $typeArray[] = TextEntry::make('category.title')->label('category');
                }
                if ($record->link === AdvertisementLink::CATEGORY) {
                    $typeArray[] = TextEntry::make('category.title')->label('category');
                }
                if ($record->link === AdvertisementLink::EXTERNAL) {
                    $typeArray[] = TextEntry::make('url');
                }

                $fileArray = [];
                $counter = 1;
                foreach ($record->files as $file) {
                    $fileArray[] = ImageEntry::make('advertisement.file.id.'.$counter)->label('file '.$counter)->state($file->url);
                    $counter++;
                }
                if ($record->link === AdvertisementLink::CATEGORY) {
                    foreach ($record->category->files as $file) {
                        $fileArray[] = ImageEntry::make('category.file.id.'.$counter)->label('file '.$counter)->state($file->url);
                        $counter++;
                    }
                }
                if ($record->link === AdvertisementLink::PRODUCT) {
                    foreach ($record->product->files as $file) {
                        $fileArray[] = ImageEntry::make('product.file.id.'.$counter)->label('file '.$counter)->state($file->url);
                        $counter++;
                    }
                }

                return [
                    Fieldset::make('Information')
                        ->columnSpanFull()
                        ->columns(4)
                        ->schema([
                            TextEntry::make('id')->numeric(),
                            TextEntry::make('title'),
                            TextEntry::make('description'),
                            TextEntry::make('order')->numeric(),
                            TextEntry::make('type')->badge(),
                            TextEntry::make('link')->badge(),
                            TextEntry::make('published_at')->dateTime(),
                            TextEntry::make('created_at')->dateTime(),
                        ]),
                    Fieldset::make('Advertisement Links')
                        ->columnSpanFull()
                        ->columns(4)
                        ->schema($typeArray),
                    Fieldset::make('Advertisement Files')
                        ->columnSpanFull()
                        ->columns(4)
                        ->schema($fileArray),
                ];
            });
    }
}
