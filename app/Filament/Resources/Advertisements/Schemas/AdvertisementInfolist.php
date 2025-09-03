<?php

namespace App\Filament\Resources\Advertisements\Schemas;

use App\Enums\AdvertisementLink;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AdvertisementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(4)
            ->components(function ($record) {
                $components = [
                    TextEntry::make('id')->numeric(),
                    TextEntry::make('title'),
                    TextEntry::make('description'),
                    TextEntry::make('order')->numeric(),
                    TextEntry::make('type')->badge(),
                    TextEntry::make('link')->badge(),
                    TextEntry::make('published_at')->dateTime(),
                    TextEntry::make('created_at')->dateTime(),
                ];

                $entryArray = [];

                if ($record->link === AdvertisementLink::PRODUCT) {
                    $entryArray[] = TextEntry::make('product.title')->label('product');
                    $entryArray[] = TextEntry::make('category.title')->label('category');
                }
                if ($record->link === AdvertisementLink::CATEGORY) {
                    $entryArray[] = TextEntry::make('category.title')->label('category');
                }
                if ($record->link === AdvertisementLink::EXTERNAL) {
                    $entryArray[] = TextEntry::make('url');
                }

                $counter = 1;
                foreach ($record->files as $file) {
                    $entryArray[] = ImageEntry::make('advertisement.file.id.'.$counter)->label('file '.$counter)->state($file->url);
                    $counter++;
                }

                if ($record->link === AdvertisementLink::CATEGORY) {
                    foreach ($record->category->files as $file) {
                        $entryArray[] = ImageEntry::make('category.file.id.'.$counter)->label('file '.$counter)->state($file->url);
                        $counter++;
                    }
                }
                if ($record->link === AdvertisementLink::PRODUCT) {
                    foreach ($record->product->files as $file) {
                        $entryArray[] = ImageEntry::make('product.file.id.'.$counter)->label('file '.$counter)->state($file->url);
                        $counter++;
                    }
                }

                $components = array_merge($components, $entryArray);

                return $components;
            });
    }
}
