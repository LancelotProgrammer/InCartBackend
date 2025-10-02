<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class ListModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'models:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all Eloquent models (excluding Pivot)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = app_path('Models');
        $files = File::allFiles($path);

        $models = [];

        foreach ($files as $file) {
            $class = 'App\\Models\\'.$file->getFilenameWithoutExtension();

            if (! class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if (
                $reflection->isSubclassOf(Model::class) &&
                ! $reflection->isSubclassOf(Pivot::class) &&
                ! $reflection->isAbstract()
            ) {
                $models[] = $reflection->getShortName();
            }
        }

        if (empty($models)) {
            $this->warn('No models found.');

            return 0;
        }

        $this->info('Models found:');
        foreach ($models as $model) {
            $this->line($model);
        }

        return 0;
    }
}
