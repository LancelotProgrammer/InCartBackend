<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Throwable;

class Optimize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (App::environment(['production', 'staging'])) {
            return;
        }

        $this->info('Optimizing application...');
        Log::debug('Commands: Optimizing application.');

        $this->commandFormat('scribe:generate', 'artisan');
        $this->commandFormat('app:delete:logs', 'artisan');
        $this->commandFormat('app:telescope:purge', 'artisan');
        $this->commandFormat('vendor\bin\pint', 'shell');
        $this->commandFormat('vendor\bin\phpstan analyse --memory-limit=2G', 'shell');
        $this->commandFormat('composer dump-autoload', 'shell');
        $this->commandFormat('optimize:clear', 'artisan');

        $this->info('Application optimized successfully.');
        Log::debug('Commands: Application optimized successfully.');

        return;
    }

    private function commandFormat(string $command, string $type): void
    {
        try {
            $this->info('------------------------------------------------------');
            $this->info("$command start");
            $this->info('');
            if ($type === 'shell') {
                $output = (string) shell_exec($command);
                $this->info($output);
            }
            if ($type === 'artisan') {
                $this->call($command);
            }
            $this->info('');
            $this->info("$command completed");
            $this->info('------------------------------------------------------');
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
