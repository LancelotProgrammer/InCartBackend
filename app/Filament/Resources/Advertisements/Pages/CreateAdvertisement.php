<?php

namespace App\Filament\Resources\Advertisements\Pages;

use App\Enums\FileType;
use App\Filament\Resources\Advertisements\AdvertisementResource;
use App\Models\File;
use Filament\Resources\Pages\CreateRecord;

class CreateAdvertisement extends CreateRecord
{
    protected static string $resource = AdvertisementResource::class;

    public function afterCreate(): void
    {
        $uploadedPaths = $this->form->getState()['files'] ?? [];

        $fileIds = [];
        foreach ($uploadedPaths as $path) {
            $file = File::create([
                'name' => basename($path),
                'type' => FileType::IMAGE->value,
                'mime' => mime_content_type(storage_path('app/public/'.$path)),
                'size' => filesize(storage_path('app/public/'.$path)),
                'url' => $path,
            ]);
            $fileIds[] = $file->id;
        }

        $this->record->files()->attach($fileIds);
    }
}
