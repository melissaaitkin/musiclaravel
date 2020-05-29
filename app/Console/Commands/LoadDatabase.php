<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Log;

class LoadDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:load';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load database from backup';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->process = new Process(sprintf(
            'mysql -u%s -p%s --port=%s %s < %s',
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.port'),
            config('database.connections.mysql.database'),
            storage_path('backups/mymusic.sql')
        ));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->process->mustRun();

            $this->info('The database load has been processed successfully.');
        } catch (ProcessFailedException $exception) {
            Log::info($exception);
            $this->error('The database load process has been failed.');
        }
    }
}