<?php

namespace App\Services;

use App\Enums\FileType;
use App\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class HandleUploadedFiles
{
    public static function configure(array|string $uploadedPaths, Model $model, string $folderName): void
    {
        $fileIds = [];

        if (! is_array($uploadedPaths)) {
            if ($uploadedPaths instanceof TemporaryUploadedFile) {
                $pathOnPublicDisk = $uploadedPaths->store($folderName, 'public');
            } else {
                $pathOnPublicDisk = $uploadedPaths;
            }

            $file = File::create([
                'name' => basename($pathOnPublicDisk),
                'type' => FileType::IMAGE->value,
                'mime' => Storage::disk('public')->mimeType($pathOnPublicDisk),
                'size' => Storage::disk('public')->size($pathOnPublicDisk),
                'url' => Storage::disk('public')->url($pathOnPublicDisk),
            ]);

            $fileIds[] = $file->id;
        } else {
            foreach ($uploadedPaths as $uploadedFile) {
                if ($uploadedFile instanceof TemporaryUploadedFile) {
                    $pathOnPublicDisk = $uploadedFile->store($folderName, 'public');
                } else {
                    $pathOnPublicDisk = $uploadedFile;
                }

                $file = File::create([
                    'name' => basename($pathOnPublicDisk),
                    'type' => FileType::IMAGE->value,
                    'mime' => Storage::disk('public')->mimeType($pathOnPublicDisk),
                    'size' => Storage::disk('public')->size($pathOnPublicDisk),
                    'url' => Storage::disk('public')->url($pathOnPublicDisk),
                ]);

                $fileIds[] = $file->id;
            }
        }

        if (! empty($fileIds)) {
            $model->files()->attach($fileIds);
        }
    }
}
