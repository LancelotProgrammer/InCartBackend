<?php

namespace App\Filament\Resources\Advertisements\Pages;

use App\Filament\Resources\Advertisements\AdvertisementResource;
use App\Services\HandleUploadedFiles;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAdvertisement extends CreateRecord
{
    protected static string $resource = AdvertisementResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $uploadedPaths = $data['file'] ?? [];

        unset($data['file']);

        $model = static::getModel()::create($data);

        HandleUploadedFiles::configure($uploadedPaths, $model, 'advertisements');

        return $model;
    }
}
