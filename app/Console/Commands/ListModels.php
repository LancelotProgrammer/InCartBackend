<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
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
    public function handle(): void
    {
        Log::channel('app_log')->debug('Commands: Listing models.');

        $path = app_path('Models');
        $files = File::allFiles($path);

        $models = [];

        foreach ($files as $file) {
            $class = 'App\\Models\\'.$file->getFilenameWithoutExtension();

            if (! class_exists($class)) {
                Log::channel('app_log')->debug('Commands: Class does not exist.', [
                    'class' => $class,
                ]);
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
            Log::channel('app_log')->debug('Commands: No models found.', [
                'models' => $models,
            ]);
            
            $this->error('No models found.');

            return;
        }

        $this->info('Models found:');
        foreach ($models as $model) {
            $this->line($model);
        }

        Log::channel('app_log')->debug('Commands: Models listed.');

        return;
    }
}
