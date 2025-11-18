<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeleteTelescopeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:telescope:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all Laravel Telescope data';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if (App::environment(['production', 'staging'])) {
            return;
        }

        $this->info('Clearing Telescope data...');
        
        Log::debug('Commands: Clearing Telescope data.');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('telescope_entries_tags')->truncate();
        DB::table('telescope_entries')->truncate();
        DB::table('telescope_monitoring')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Telescope data cleared successfully.');
        
        Log::debug('Commands: Telescope data cleared successfully.');

        return;
    }
}
