<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        $logPath = storage_path('logs');

        if (! is_dir($logPath)) {
            $this->error('Logs directory does not exist.');

            return;
        }

        $files = glob($logPath.'/*.log');

        if (empty($files)) {
            $this->info('No log files found.');

            return;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $this->info('All log files have been deleted.');
    }
}
