<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\RelationManagers\BaseFilesRelationManager;

class FilesRelationManager extends BaseFilesRelationManager
{
    protected static bool $isLazy = false;

    protected static string $directory = 'categories';
}
