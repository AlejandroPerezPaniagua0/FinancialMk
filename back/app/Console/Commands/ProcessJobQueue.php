<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessJobQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process jobs from the queue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing jobs...');

        $this->call('queue:work', [
            '--stop-when-empty' => true,
        ]);

        $this->info('Done.');
    }
}