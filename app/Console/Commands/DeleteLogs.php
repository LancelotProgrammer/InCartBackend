<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class DeleteLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete:logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete all logs';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (App::environment(['production', 'staging'])) {
            return;
        }

        $this->info('Deleting all logs...');

        Log::debug('Commands: Deleting all logs.');

        $logPath = storage_path('logs');

        if (! is_dir($logPath)) {
            Log::debug('Commands: Logs directory does not exist.');
            
            $this->error('Logs directory does not exist.');

            return;
        }

        $files = glob($logPath.'/*.log');

        if (empty($files)) {
            Log::debug('Commands: No log files found.');
            
            $this->error('No log files found.');

            return;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $this->info('All log files have been deleted.');
        
        Log::debug('Commands: All log files have been deleted.');

        return;
    }
}
